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
namespace AdminBundle\Service\TwineImport;

use DembeloMain\Document\Importfile;
use DembeloMain\Service\FileHandler;

/**
 * Class ImportTwine
 */
class ImportTwine
{
    /**
     * @var resource
     */
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
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * ImportTwine constructor.
     * @param FileExtractor     $fileExtractor
     * @param FileCheck         $fileCheck
     * @param StoryDataParser   $storyDataParser
     * @param PassageDataParser $passageDataParser
     * @param ParserContext     $parserContext
     * @param FileHandler       $fileHandler
     */
    public function __construct(FileExtractor $fileExtractor, FileCheck $fileCheck, StoryDataParser $storyDataParser, PassageDataParser $passageDataParser, ParserContext $parserContext, FileHandler $fileHandler)
    {
        $this->fileExtractor = $fileExtractor;
        $this->fileCheck = $fileCheck;
        $this->storyDataParser = $storyDataParser;
        $this->passageDataParser = $passageDataParser;
        $this->parserContext = $parserContext;
        $this->fileHandler = $fileHandler;
    }

    /**
     * main method, starts the import process
     *
     * @param Importfile $importfile
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function run(Importfile $importfile): bool
    {
        $this->parserContext->init($importfile);
        $this->storyDataParser->setParserContext($this->parserContext);
        $this->passageDataParser->setParserContext($this->parserContext);

        $filenameExtracted = $this->fileExtractor->extract($this->parserContext->getFilename());

        $fileHandler = $this->fileHandler->open($filenameExtracted, 'rb');

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

    /**
     * @throws \Exception
     */
    private function setXmlHandler(): void
    {
        xml_parser_set_option($this->xmlParser, XML_OPTION_CASE_FOLDING, 0);

        if (xml_set_element_handler($this->xmlParser, [$this, 'startElement'], [$this, 'endElement']) !== true) {
            throw new \Exception('Couldn\'t register start/end event handlers for the XML parser.');
        }

        if (xml_set_character_data_handler($this->xmlParser, [$this, 'characterData']) !== true) {
            throw new \Exception('Couldn\'t register character data event handler for the XML parser.');
        }
    }

    /**
     * @throws \Exception
     */
    private function parseEnvelopeHeader(): void
    {
        if (xml_parse($this->xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n") !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception(sprintf('Error #%d: \'%s\' occurred while the envelope head for the Twine archive was parsed.', $errorCode, $errorDescription));
        }
    }

    /**
     * @throws \Exception
     */
    private function parseEnvelopeFooter(): void
    {
        if (xml_parse($this->xmlParser, "\n</tw-archive>\n", true) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception(sprintf("Error #%d: '%s' occurred while the envelope foot for the Twine archive was parsed.", $errorCode, $errorDescription));
        }
    }

    /**
     * @param FileHandler $fileHandler
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function initParser(FileHandler $fileHandler): bool
    {
        $this->setXmlHandler();

        $this->parseEnvelopeHeader();

        do {
            if (!$this->parseLine($fileHandler)) {
                break;
            }
        } while ($fileHandler->eof() === false);

        $this->parseEnvelopeFooter();

        $this->parserFree();

        // PHP 5.4 compatibility: there's no 'finally' yet.
        return $fileHandler->close() !== false;
    }

    /**
     * @param FileHandler $fileHandler
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function parseLine(FileHandler $fileHandler): bool
    {
        $buffer = $fileHandler->read(4096);

        if (false === $buffer) {
            return false;
        }
        if (xml_parse($this->xmlParser, $buffer) !== 1) {
            $this->throwParserException();
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    private function throwParserException(): void
    {
        $errorCode = xml_get_error_code($this->xmlParser);
        $errorDescription = xml_error_string($errorCode);
        $errorRowNumber = xml_get_current_line_number($this->xmlParser);
        $errorColumnNumber = xml_get_current_column_number($this->xmlParser);
        $errorByteIndex = xml_get_current_byte_index($this->xmlParser);

        throw new \Exception(sprintf('Error #%s: \'%s\' occurred while parsing the Twine archive file \'%s\' in line %d, character %d (at byte index %d).', $errorCode, $errorDescription, $this->parserContext->getFilename(), $errorRowNumber, $errorColumnNumber, $errorByteIndex));
    }

    /**
     * @param resource $parser
     * @param string   $name
     * @param array    $attrs
     *
     * @return void
     *
     * @throws \Exception
     */
    private function startElement($parser, string $name, array $attrs): void
    {
        if ('tw-storydata' === $name) {
            $this->storyDataParser->startElement($name, $attrs);
        } elseif ($this->parserContext->isTwineRelevant() && 'tw-passagedata' === $name) {
            $this->passageDataParser->startElement($name, $attrs);
        }
    }

    /**
     * @param resource $parser
     * @param string   $data
     */
    private function characterData($parser, string $data): void
    {
        if ($this->parserContext->isTwineRelevant() && $this->parserContext->isTwineText()) {
            $this->parserContext->getCurrentTextnode()->setText($this->parserContext->getCurrentTextnode()->getText().$data);
        }
    }

    /**
     * xml_parse method
     *
     * @param resource $parser
     * @param string   $name
     *
     * @throws \Exception
     */
    private function endElement($parser, string $name): void
    {
        if ('tw-storydata' === $name) {
            $this->storyDataParser->endElement($name);
        } elseif ($this->parserContext->isTwineRelevant() && 'tw-passagedata' === $name) {
            $this->passageDataParser->endElement();
        }
    }
}
