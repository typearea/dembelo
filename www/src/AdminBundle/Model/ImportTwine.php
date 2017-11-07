<?php
/* Copyright (C) 2016 Michael Giesler
 *
 * This file is part of Dembelo.
 *
 * Dembelo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Dembelo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Dembelo. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * @package AdminBundle
 */

namespace AdminBundle\Model;

use AdminBundle\Service\TwineImport\FileCheck;
use AdminBundle\Service\TwineImport\FileExtractor;
use AdminBundle\Service\TwineImport\ParserContext;
use AdminBundle\Service\TwineImport\PassageDataParser;
use AdminBundle\Service\TwineImport\StoryDataParser;
use DembeloMain\Document\Importfile;

/**
 * Class ImportTwine
 * @package AdminBundle\Model
 */
class ImportTwine
{
    private $xmlParser;

    /**
     * @var FileExtractor
     */
    private $fileExtractor;

    /**
     * @var FileCheck
     */
    private $fileCheck;

    /**
     * @var StoryDataParser
     */
    private $storyDataParser;

    /**
     * @var PassageDataParser
     */
    private $passageDataParser;

    /**
     * @var ParserContext
     */
    private $parserContext;

    /**
     * ImportTwine constructor.
     * @param FileExtractor     $fileExtractor
     * @param FileCheck         $fileCheck
     * @param StoryDataParser   $storyDataParser
     * @param PassageDataParser $passageDataParser
     * @param ParserContext     $parserContext
     */
    public function __construct(
        FileExtractor $fileExtractor,
        FileCheck $fileCheck,
        StoryDataParser $storyDataParser,
        PassageDataParser $passageDataParser,
        ParserContext $parserContext
    ) {
        $this->fileExtractor = $fileExtractor;
        $this->fileCheck = $fileCheck;
        $this->storyDataParser = $storyDataParser;
        $this->passageDataParser = $passageDataParser;
        $this->parserContext = $parserContext;
    }

    /**
     * main method, starts the import process
     *
     * @param Importfile $importfile
     * @return bool
     * @throws \Exception
     */
    public function run(Importfile $importfile): bool
    {
        $this->parserContext->init($importfile);
        $this->storyDataParser->setParserContext($this->parserContext);
        $this->passageDataParser->setParserContext($this->parserContext);

        $filenameExtracted = $this->fileExtractor->extract($this->parserContext->getFilename());

        $fileHandler = fopen($filenameExtracted, 'rb');

        if ($fileHandler === false) {
            throw new \Exception('Couldn\'t open file \''.$this->parserContext->getFilename().'\'');
        }

        $this->xmlParser = xml_parser_create('UTF-8');

        $this->fileCheck->check($fileHandler, $this->parserContext->getFilename());

        if (!$this->initParser($fileHandler)) {
            return false;
        }

        return true;
    }

    /**
     * destroys the parser object
     */
    public function parserFree(): void
    {
        xml_parser_free($this->xmlParser);
    }

    private function setXmlHandler(): void
    {
        xml_parser_set_option($this->xmlParser, XML_OPTION_CASE_FOLDING, 0);

        if (xml_set_element_handler($this->xmlParser, array($this, "startElement"), array($this, "endElement")) !== true) {
            throw new \Exception("Couldn't register start/end event handlers for the XML parser.");
        }

        if (xml_set_character_data_handler($this->xmlParser, array($this, "characterData")) !== true) {
            throw new \Exception("Couldn't register character data event handler for the XML parser.");
        }
    }

    private function parseEnvelopeHeader(): void
    {
        if (xml_parse($this->xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n") !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope head for the Twine archive was parsed.");
        }
    }

    private function parseEnvelopeFooter(): void
    {
        if (xml_parse($this->xmlParser, "\n</tw-archive>\n", true) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope foot for the Twine archive was parsed.");
        }
    }

    private function initParser($fileHandler): bool
    {
        $this->setXmlHandler();

        $this->parseEnvelopeHeader();

        do {
            if (!$this->parseLine($fileHandler)) {
                break;
            }
        } while (feof($fileHandler) === false);

        $this->parseEnvelopeFooter();

        $this->parserFree();

        // PHP 5.4 compatibility: there's no 'finally' yet.
        return !(fclose($fileHandler) === false);
    }

    private function parseLine($fileHandler): bool
    {
        $buffer = fread($fileHandler, 4096);

        if ($buffer === false) {
            return false;
        }
        if (xml_parse($this->xmlParser, $buffer) !== 1) {
            $this->throwParserException();
        }

        return true;
    }

    private function throwParserException(): void
    {
        $errorCode = xml_get_error_code($this->xmlParser);
        $errorDescription = xml_error_string($errorCode);
        $errorRowNumber = xml_get_current_line_number($this->xmlParser);
        $errorColumnNumber = xml_get_current_column_number($this->xmlParser);
        $errorByteIndex = xml_get_current_byte_index($this->xmlParser);

        throw new \Exception('Error #'.$errorCode.': \''.$errorDescription."' occurred while parsing the Twine archive file '".$this->parserContext->getFilename()."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").");
    }

    private function startElement($parser, string $name, array $attrs): void
    {
        if ($name === 'tw-storydata') {
            $this->storyDataParser->startElement($name, $attrs);
        } elseif ($this->parserContext->isTwineRelevant() && $name === 'tw-passagedata') {
            $this->passageDataParser->startElement($name, $attrs);
        }
    }

    private function characterData($parser, string $data): void
    {
        if ($this->parserContext->isTwineRelevant() && $this->parserContext->isTwineText()) {
            $this->parserContext->getCurrentTextnode()->setText($this->parserContext->getCurrentTextnode()->getText().$data);
        }
    }

    /**
     * xml_parse method
     *
     * @param $parser
     * @param string $name
     * @throws \Exception
     */
    private function endElement($parser, string $name): void
    {
        if ($name === 'tw-storydata') {
            $this->storyDataParser->endElement($name);
        } elseif ($this->parserContext->isTwineRelevant() && $name === 'tw-passagedata') {
            $this->passageDataParser->endElement();
        }
    }
}
