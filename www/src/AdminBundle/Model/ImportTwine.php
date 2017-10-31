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
use AdminBundle\Service\TwineImport\HitchParser;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Exception;

/**
 * Class ImportTwine
 * @package AdminBundle\Model
 */
class ImportTwine
{
    /**
     * @var TextnodeRepositoryInterface
     */
    private $textnodeRepository;

    private $xmlParser;

    /**
     * @var Importfile
     */
    private $importfile;

    /**
     * @var \DembeloMain\Document\Textnode
     */
    private $textnode = null;
    private $nodeNameMapping = [];
    private $accessSet = false;
    private $twineId;

    private $twineRelevant = false;
    private $twineStartnodeId = -1;
    private $twineTextnodeName = null;
    private $twineText = false;

    /**
     * @var string[]
     */
    private $textnodeMapping;

    private $hitchParser;

    /**
     * @var FileExtractor
     */
    private $fileExtractor;

    /**
     * @var FileCheck
     */
    private $fileCheck;

    /**
     * ImportTwine constructor.
     * @param TextnodeRepositoryInterface $textnodeRepository
     * @param HitchParser $hitchParser
     * @param FileExtractor $fileExtractor
     * @param FileCheck $fileCheck
     */
    public function __construct(
        TextNodeRepositoryInterface $textnodeRepository,
        HitchParser $hitchParser,
        FileExtractor $fileExtractor,
        FileCheck $fileCheck
    ) {
        $this->textnodeRepository = $textnodeRepository;
        $this->hitchParser = $hitchParser;
        $this->fileExtractor = $fileExtractor;
        $this->fileCheck = $fileCheck;
    }

    /**
     * main method, starts the import process
     *
     * @param Importfile $importfile
     * @return bool
     * @throws Exception
     */
    public function run(Importfile $importfile): bool
    {
        $this->importfile = $importfile;

        if (null === $this->importfile->getLicenseeId()) {
            throw new Exception('no licensee available');
        }

        if (null === $this->importfile->getFilename()) {
            throw new Exception('no filename available');
        }
        $filenameExtracted = $this->fileExtractor->extract($this->importfile->getFilename());

        $fileHandler = fopen($filenameExtracted, "rb");

        if ($fileHandler === false) {
            throw new Exception("Couldn't open file '".$this->importfile->getFilename()."'");
        }

        $this->xmlParser = xml_parser_create("UTF-8");

        $this->fileCheck->check($fileHandler, $this->importfile->getFilename());

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
            throw new Exception("Couldn't register start/end event handlers for the XML parser.");
        }

        if (xml_set_character_data_handler($this->xmlParser, array($this, "characterData")) !== true) {
            throw new Exception("Couldn't register character data event handler for the XML parser.");
        }
    }

    private function parseEnvelopeHeader(): void
    {
        if (xml_parse($this->xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n", false) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope head for the Twine archive was parsed.");
        }
    }

    private function parseEnvelopeFooter(): void
    {
        if (xml_parse($this->xmlParser, "\n</tw-archive>\n", true) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope foot for the Twine archive was parsed.");
        }
    }

    private function initParser($fileHandler)
    {
        $this->setXmlHandler();

        $this->parseEnvelopeHeader();

        do {
            $buffer = fread($fileHandler, 4096);

            if ($buffer === false) {
                break;
            }
            if (xml_parse($this->xmlParser, $buffer, false) !== 1) {
                $errorCode = xml_get_error_code($this->xmlParser);
                $errorDescription = xml_error_string($errorCode);
                $errorRowNumber = xml_get_current_line_number($this->xmlParser);
                $errorColumnNumber = xml_get_current_column_number($this->xmlParser);
                $errorByteIndex = xml_get_current_byte_index($this->xmlParser);

                throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while parsing the Twine archive file '".$this->importfile->getFilename()."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").");
            }
        } while (feof($fileHandler) === false);

        $this->parseEnvelopeFooter();

        $this->parserFree();

        // PHP 5.4 compatibility: there's no 'finally' yet.
        if (fclose($fileHandler) === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param array $attrs
     * @throws Exception
     */
    private function startElementStoryData(string $name, array $attrs): void
    {
        if ($this->twineRelevant === true) {
            throw new Exception("Nested '".$name."' found in Twine archive file '".$this->importfile->getFilename()."'.");
        }

        if (isset($attrs['startnode']) !== true) {
            return;
        }

        if (is_numeric($attrs['startnode']) !== true) {
            return;
        }

        if (isset($attrs['name']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->importfile->getFilename()."' which is missing its 'name' attribute.");
        }

        $this->twineStartnodeId = $attrs['startnode'];
        $this->textnodeMapping = [];
        $this->twineRelevant = true;
    }

    private function getTwineId(string $tagString, string $textnodeTitle): string
    {
        if (empty($tagString) || !is_string($tagString)) {
            throw new Exception('no ID given for Textnode "'.$textnodeTitle.'"');
        }
        $tagArray = explode(' ', $tagString);

        $twineId = false;

        foreach ($tagArray as $tag) {
            if (0 === strpos($tag, 'ID:')) {
                $twineId = substr($tag, 3);
            }
        }

        if ($twineId === false) {
            throw new Exception('no ID given for Textnode "'.$textnodeTitle.'"');
        }

        return $twineId;
    }

    private function startElementPassageData(string $name, array $attrs): void
    {
        if ($this->twineText !== false) {
            throw new Exception("Nested '".$name."' found in Twine archive file '".$this->importfile->getFilename()."'.");
        }

        if (isset($attrs['pid']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->importfile->getFilename()."' which is missing its 'pid' attribute.");
        }

        if (is_numeric($attrs['pid']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->importfile->getFilename()."' which hasn't a numeric value in its 'pid' attribute ('".$attrs['pid']."' was found instead).");
        }

        $this->twineId = $this->getTwineId($attrs['tags'], $attrs['name']);

        if (array_key_exists($this->twineId, $this->textnodeMapping) === true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->importfile->getFilename()."' which has a non unique 'id' tag [".$this->twineId."], in node '".$attrs['name']."'");
        }

        $this->twineTextnodeName = $attrs['name'];

        $this->textnode = $this->textnodeRepository->findByTwineId($this->importfile, $this->twineId);
        if (null === $this->textnode) {
            $this->textnode = new Textnode();
            $this->textnode->setCreated(date('Y-m-d H:i:s'));
            $this->textnode->setTopicId($this->importfile->getTopicId());
            $this->textnode->setLicenseeId($this->importfile->getLicenseeId());
            $this->textnode->setImportfileId($this->importfile->getId());
            $this->textnode->setStatus(Textnode::STATUS_ACTIVE);
            $this->textnode->setTwineId($this->twineId);
        } else {
            $this->textnode->setText('');
            $this->textnode->clearHitches();
        }

        $this->textnode->setMetadata(
            array(
                'Titel' => $this->twineTextnodeName,
                'Autor' => $this->importfile->getAuthor(),
                'Verlag' => $this->importfile->getPublisher(),
            )
        );

        if ($attrs['pid'] == $this->twineStartnodeId) {
            if ($this->accessSet !== true) {
                $this->textnode->setAccess(true);
                $this->accessSet = true;
            } else {
                throw new Exception('There is more than one \''.$name.'\' in the Twine archive file \''.$this->importfile->getFilename().'\' with the startnode value \''.$attrs['pid'].'\' in its \'pid\' attribute.');
            }
        } else {
            $this->textnode->setAccess(false);
        }

        $this->twineText = true;
    }

    private function startElement($parser, string $name, array $attrs)
    {
        if ($name === 'tw-storydata') {
            $this->startElementStoryData($name, $attrs);
        } elseif ($this->twineRelevant === true && $name === 'tw-passagedata') {
            $this->startElementPassageData($name, $attrs);
        }
    }

    private function characterData($parser, string $data)
    {
        if ($this->twineRelevant === true && $this->twineText === true) {
            $this->textnode->setText($this->textnode->getText().$data);
        }
    }

    private function parseText(Textnode $textnode, string $text, string $name): ?string
    {
        $textnodeTextNew = preg_replace_callback(
            '/\[\[(.*?)\]\]/',
            function($matches) use ($textnode, $name) {
                $content = $matches[1];
                $hitch = null;
                $metadata = null;
                if (strpos($content, '-->') !== false) {
                    $hitch = $this->hitchParser->parseDoubleArrowRight($content, $name);
                } elseif (strpos($content, '->') !== false) {
                    $hitch = $this->hitchParser->parseSingleArrowRight($content, $name);
                } elseif (strpos($content, '<-') !== false) {
                    $hitch = $this->hitchParser->parseSingleArrowLeft($content, $name);
                } elseif (strpos($content, '>:<') !== false) {
                    $metadata = $this->parseColonArrows($textnode, $content, $name);
                    $textnode->setMetadata($metadata);
                } else {
                    $hitch = $this->hitchParser->parseSimpleHitch($content, $name);
                }

                $this->appendHitchToTextnode($textnode, $hitch);
            },
            $text
        );

        return trim($textnodeTextNew);
    }

    /**
     * @param $name
     * @param $dembeloId
     * @throws Exception
     */
    private function endElementStoryDataForOneTextnode(string $name, string $dembeloId): void
    {
        $textnode = $this->textnodeRepository->find($dembeloId);

        if (null === $textnode) {
            throw new Exception('The Dembelo Textnode with Id \''.$dembeloId.'\' doesn\'t exist, but should by now.');
        }

        $textnodeText = $textnode->getText();
        $textnodeTextNew = $this->parseText($textnode, $textnodeText, $name);

        if (null !== $textnodeTextNew) {
            $textnodeTextNew = $this->convertToPTags($textnodeTextNew);
        }

        $textnode->setText($textnodeTextNew);
        $this->textnodeRepository->setHyphenatedText($textnode);
    }

    /**
     * @param Textnode $textnode
     * @param array|null $hitch
     * @throws Exception
     */
    private function appendHitchToTextnode(Textnode $textnode, ?array $hitch): void
    {
        if ($hitch === null) {
            return;
        }
        if ($textnode->getHitchCount() >= Textnode::HITCHES_MAXIMUM_COUNT) {
            throw new Exception('There is a textnode in the Twine archive file which has more than '.Textnode::HITCHES_MAXIMUM_COUNT.' links.');
        }

        if ($textnode->appendHitch($hitch) !== true) {
            throw new Exception('Failed to append hitch for a textnode');
        }
    }

    /**
     * @param Textnode $textnode
     * @param string $content
     * @param string $name
     * @return array
     * @throws Exception
     */
    private function parseColonArrows(Textnode $textnode, string $content, string $name): array
    {
        $contentArray = explode(">:<", $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new Exception("The Twine archive file contains a '".$name."' with the invalid element '[[".$contentArray[0].">:<".$contentArray[1]."]]'.");
        }

        $metadata = $textnode->getMetadata();

        if (is_array($metadata) !== true) {
            $metadata = array();
        }

        if (array_key_exists($contentArray[0], $metadata) === true) {
            throw new Exception("There is a textnode in the Twine archive file which contains the metadata field '".$contentArray[0]."' twice or would overwrite the already existing value of that field.");
        }

        $metadata[$contentArray[0]] = $contentArray[1];

        return $metadata;
    }

    private function convertToPTags(string $textnodeText): string
    {
        $textnodeTextLength = strlen($textnodeText);
        $textnodeTextNew = '<p>';
        $consumed = 0;
        for ($i = 0; $i < $textnodeTextLength; $i++) {
            if ($textnodeText[$i] === "\n" || $textnodeText[$i] === "\r") {
                $consumed++;

                continue;
            }
            if ($consumed > 0 && $i > $consumed) {
                $textnodeTextNew .= '</p><p>';
            }

            $textnodeTextNew .= $textnodeText[$i];
            $consumed = 0;
        }

        $textnodeTextNew .= '</p>';

        return $textnodeTextNew;
    }

    private function endElementStoryData(string $name): void
    {
        foreach ($this->textnodeMapping as $dembeloId) {
            $this->endElementStoryDataForOneTextnode($name, $dembeloId);
        }

        $this->textnodeRepository->disableOrphanedNodes($this->importfile, array_values($this->textnodeMapping));

        $this->twineRelevant = false;
        $this->twineStartnodeId = -1;
        $this->textnodeMapping = null;
        $this->accessSet = false;
    }

    private function endElementPassageData(): void
    {
        $this->textnodeRepository->save($this->textnode);

        $this->textnodeMapping[$this->twineId] = $this->textnode->getId();
        $this->nodeNameMapping[$this->twineTextnodeName] = $this->textnode->getId();
        $this->twineTextnodeName = null;

        $this->twineText = false;
        $this->textnode = null;
    }

    /**
     * xml_parse method
     *
     * @param $parser
     * @param string $name
     * @throws Exception
     */
    private function endElement($parser, string $name): void
    {
        if ($name === 'tw-storydata') {
            $this->endElementStoryData($name);
        } elseif ($this->twineRelevant === true && $name === 'tw-passagedata') {
            $this->endElementPassageData();
        }
    }
}
