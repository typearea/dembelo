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


namespace AdminBundle;


use DembeloMain\Document\Textnode;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportTwine
{
    /** @var ContainerInterface */
    private $container;

    private $xmlParser;

    private $twineArchivePath;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm = null;

    /**
     * @var DocumentRepository
     */
    private $repositoryTopic = null;

    private $licenseeId = null;
    private $author = "";
    private $publisher = "";

    /**
     * @var \DembeloMain\Document\Textnode
     */
    private $textnode = null;
    private $topicId = null;
    private $accessSet = false;

    private $twineRelevant = false;
    private $twineStartnodeId = -1;
    private $twineTextnodeName = null;
    private $twineText = false;

    private $textnodeMapping;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run($twineArchivePath, $licenseeId, $author, $publisher)
    {
        $this->licenseeId = $licenseeId;
        $this->author = $author;
        $this->publisher = $publisher;

        $twineArchivePathExtracted = $this->extractTwineFile($twineArchivePath);

        $fileHandler = fopen($twineArchivePathExtracted, "rb");

        if ($fileHandler === false) {
            throw new Exception("Couldn't open file '" . $twineArchivePath . "'");
        }

        $this->xmlParser = xml_parser_create("UTF-8");

        $this->checkTwineFile($fileHandler);

        if (!$this->initParser($fileHandler)) {
            return false;
        }

        return true;
    }

    public function parserFree()
    {
        xml_parser_free($this->xmlParser);
    }

    private function extractTwineFile($file)
    {
        $extractedFile = $file.'.extracted';

        $fileHandle = fopen($file, 'r');
        $extractedFileHandle = fopen($extractedFile, 'w');
        if ($fileHandle === false) {
            throw new Exception("Failed to read data from file '".$this->twineArchivePath."'.");
        }
        $writing = false;
        $matches = array();
        while (($row = fgets($fileHandle)) !== false) {
            if ($writing) {
                if (preg_match('(^.*</tw-storydata>)', $row, $matches)) {
                    fputs($extractedFileHandle, $matches[0]);
                    break;
                }
                fputs($extractedFileHandle, $row);
            } else {
                if (preg_match('(<tw-storydata.*$)', $row, $matches)) {
                    fputs($extractedFileHandle, $matches[0]);
                    $writing = true;
                }
            }
        }

        fclose($fileHandle);
        fclose($extractedFileHandle);

        return $extractedFile;
    }

    private function checkTwineFile($fileHandler)
    {
        $magicString = "<tw-storydata ";
        $magicStringLength = strlen($magicString);

        $peekData = fread($fileHandler, 1024);

        if ($peekData === false) {
            throw new Exception("Failed to read data from file '".$this->twineArchivePath."'.");
        }

        $peekDataLength = strlen($peekData);

        if ($peekDataLength <= 0) {
            throw new Exception("<warning>File '".$this->twineArchivePath."' seems to be empty.</warning>");
        }

        for ($i = 0; $i < $peekDataLength; $i++) {
            if ($peekData[$i] === ' ' ||
                $peekData[$i] === '\n' ||
                $peekData[$i] === '\r' ||
                $peekData[$i] === '\t') {
                // Consume whitespace.
                continue;
            }

            if ($peekDataLength - $i < $magicStringLength) {
                throw new Exception("File '".$this->twineArchivePath."' isn't a Twine archive file.");
            }

            if (substr($peekData, $i, $magicStringLength) !== $magicString) {
                throw new Exception("File '".$this->twineArchivePath."' isn't a Twine archive file.");
            }

            if (fseek($fileHandler, 0) !== 0) {
                throw new Exception("Couldn't reset reading position after the magic string in the Twine archive file '".$this->twineArchivePath."' was checked.");
            }

            return true;
        }

        throw new Exception("File '".$this->twineArchivePath."' doesn't seem to be a Twine archive file.");
    }

    private function initParser($fileHandler)
    {
        xml_parser_set_option($this->xmlParser, XML_OPTION_CASE_FOLDING, 0);

        if (xml_set_element_handler($this->xmlParser, array($this, "startElement"), array($this, "endElement")) !== true) {
            throw new Exception("Couldn't register start/end event handlers for the XML parser.");
        }

        if (xml_set_character_data_handler($this->xmlParser, array($this, "characterData")) !== true) {
            throw new Exception("Couldn't register character data event handler for the XML parser.");
        }

        $this->repositoryTopic = $this->container->get('doctrine_mongodb')->getRepository('DembeloMain:Topic');

        /** @todo This should be part of the Twine export. */
        if (xml_parse($this->xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n", false) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope head for the Twine archive was parsed.");
        }

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

                throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while parsing the Twine archive file '".$this->twineArchivePath."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").");
            }
        } while (feof($fileHandler) === false);

        if (xml_parse($this->xmlParser, "\n</tw-archive>\n", true) !== 1) {
            $errorCode = xml_get_error_code($this->xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new Exception("Error #".$errorCode.": '".$errorDescription."' occurred while the envelope foot for the Twine archive was parsed.");
        }

        xml_parser_free($this->xmlParser);

        // PHP 5.4 compatibility: there's no 'finally' yet.
        if (fclose($fileHandler) === false) {
            return false;
        }

        return true;
    }

    private function startElementStoryData($name, $attrs)
    {
        if ($this->twineRelevant === true) {
            throw new Exception("Nested '".$name."' found in Twine archive file '".$this->twineArchivePath."'.");
        }

        if (isset($attrs['startnode']) !== true) {
            return;
        }

        if (is_numeric($attrs['startnode']) !== true) {
            return;
        }

        if (isset($attrs['name']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'name' attribute.");
        }

        $twineStory = explode("-->", $attrs['name'], 2);

        if (count($twineStory) !== 2) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which has an incomplete 'name' attribute. Twine stories must use the naming schema '?-->story name', where '?' is an existing Dembelo Topic Id. Instead, '".$attrs['name']."' was found.");
        }

        $this->topicId = $twineStory[0];

        $topic = $this->repositoryTopic->find($this->topicId);

        if (is_null($topic)) {
            throw new Exception("The Dembelo Topic with Id '".$this->topicId."', referenced by Twine story '".$attrs['name']."' in the Twine archive file '".$this->twineArchivePath."', doesn't exist.");
        }

        $this->twineStartnodeId = $attrs['startnode'];
        $this->textnodeMapping = array();
        $this->twineRelevant = true;
    }

    private function startElementPassageData($name, $attrs)
    {
        if ($this->twineText !== false) {
            throw new Exception("Nested '".$name."' found in Twine archive file '".$this->twineArchivePath."'.");
        }

        if (isset($attrs['pid']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'pid' attribute.");
        }

        if (is_numeric($attrs['pid']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which hasn't a numeric value in its 'pid' attribute ('".$attrs['pid']."' was found instead).");
        }

        if (isset($attrs['name']) !== true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'name' attribute.");
        }

        if (array_key_exists($attrs['name'], $this->textnodeMapping) === true) {
            throw new Exception("There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which has a 'name' attribute with value '".$attrs['name']."'. This value is used more than once, while it must be unique.");
        }

        $this->twineTextnodeName = $attrs['name'];

        $this->textnode = new Textnode();
        $this->textnode->setStatus(Textnode::STATUS_ACTIVE);
        $this->textnode->setCreated(date('Y-m-d H:i:s'));
        $this->textnode->setTopicId($this->topicId);
        $this->textnode->setLicenseeId($this->licenseeId);
        $this->textnode->setMetadata(
            array(
                'Titel' => $this->twineTextnodeName,
                'Autor' => $this->author,
                'Verlag' => $this->publisher,
            )
        );

        if ($attrs['pid'] == $this->twineStartnodeId) {
            if ($this->accessSet !== true) {
                $this->textnode->setAccess(true);
                $this->accessSet = true;
            } else {
                throw new Exception("There is more than one '".$name."' in the Twine archive file '".$this->twineArchivePath."' with the startnode value '".$attrs['pid']."' in its 'pid' attribute.");
            }
        } else {
            $this->textnode->setAccess(false);
        }

        $this->twineText = true;
    }

    private function startElement($parser, $name, $attrs)
    {
        if ($name === "tw-storydata") {
            $this->startElementStoryData($name, $attrs);
        } elseif ($this->twineRelevant === true && $name === "tw-passagedata") {
            $this->startElementPassageData($name, $attrs);
        }
    }

    private function characterData($parser, $data)
    {
        if ($this->twineRelevant === true && $this->twineText === true) {
            $this->textnode->setText($this->textnode->getText().$data);
        }
    }

    private function endElementStoryData($name)
    {
        foreach ($this->textnodeMapping as $twineName => $dembeloId) {
            $textnode = $this->dm->find("DembeloMain:Textnode", $dembeloId);

            if (is_null($textnode) === true) {
                throw new Exception("The Dembelo Textnode with Id '".$dembeloId."' doesn't exist, but should by now.");
            }

            /** @todo The links should be exported as XML as well instead of a custom Twine inline format. */

            $textnodeText = $textnode->getText();
            $startPos = strpos($textnodeText, "[[", 0);

            if ($startPos !== false) {
                $textnodeTextNew = substr($textnodeText, 0, $startPos);

                while ($startPos !== false) {
                    $endPos = strpos($textnodeText, "]]", $startPos + strlen("[["));

                    if ($endPos === false) {
                        throw new Exception("The Twine archive file '".$this->twineArchivePath."' has a textnode named '".$twineName."' which contains a malformed link that starts with '[[' but has no corresponding ']]'.");
                    }

                    $content = substr($textnodeText, $startPos + strlen("[["), $endPos - ($startPos + strlen("[[")));
                    $hitch = null;
                    $metadata = null;

                    if (strpos($content, "-->") !== false) {
                        $content = explode("-->", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new Exception("The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."-->".$content[1]."]]'.");
                        }

                        /**
                         * @var $repositoryTextnode DocumentRepository
                         */
                        $repositoryTextnode = $this->container->get('doctrine_mongodb')->getRepository('DembeloMain:Textnode');

                        $externalTextnode = $repositoryTextnode->createQueryBuilder()
                            ->field('id')->equals(new \MongoId($content[1]))
                            ->getQuery()->getSingleResult();

                        if (is_null($externalTextnode)) {
                            throw new Exception("There is a textnode named '".$twineName."' in the Twine archive file '".$this->twineArchivePath."' which references the external Dembelo Textnode '".$content[1]."', but a Dembelo Textnode with such an Id doesn't exist.");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[0];
                        $hitch['textnodeId'] = $content[1];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, "->") !== false) {
                        $content = explode("->", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new Exception("The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."->".$content[1]."]]'.");
                        }

                        if (array_key_exists($content[1], $this->textnodeMapping) !== true) {
                            throw new Exception("There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content[1]."', but this textnode doesn't exist within the same story.");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[0];
                        $hitch['textnodeId'] = $this->textnodeMapping[$content[1]];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, "<-") !== false) {
                        $content = explode("<-", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new Exception("The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."<-".$content[1]."]]'.");
                        }

                        if (array_key_exists($content[0], $this->textnodeMapping) !== true) {
                            throw new Exception("There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content[0]."', but this textnode doesn't exist within the same story.");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[1];
                        $hitch['textnodeId'] = $this->textnodeMapping[$content[0]];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, ">:<") !== false) {
                        $content = explode(">:<", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new Exception("The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0].">:<".$content[1]."]]'.");
                        }

                        $metadata = $textnode->getMetadata();

                        if (is_array($metadata) != true) {
                            $metadata = array();
                        }

                        if (array_key_exists($content[0], $metadata) === true) {
                            throw new Exception("There is a textnode in the Twine archive file '".$this->twineArchivePath."' which contains the metadata field '".$content[0]."' twice or would overwrite the already existing value of that field.");
                        }

                        $metadata[$content[0]] = $content[1];
                    } else {
                        if (strlen($content) <= 0) {
                            throw new Exception("The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content."]]'.");
                        }

                        if (array_key_exists($content, $this->textnodeMapping) !== true) {
                            throw new Exception("There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content."', but this textnode doesn't exist within the same story.");
                        }

                        $hitch = array();
                        $hitch['description'] = $content;
                        $hitch['textnodeId'] = $this->textnodeMapping[$content];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    }

                    if ($hitch !== null) {
                        if ($textnode->getHitchCount() >= Textnode::HITCHES_MAXIMUM_COUNT) {
                            throw new Exception("There is a textnode named '".$twineName."' in the Twine archive file '".$this->twineArchivePath."' which has more than ".Textnode::HITCHES_MAXIMUM_COUNT." links.");
                        }

                        if ($textnode->appendHitch($hitch) !== true) {
                            throw new Exception("Failed to append hitch for the textnode named '".$twineName."' from the Twine archive file '".$this->twineArchivePath."'.");
                        }
                    }

                    if ($metadata !== null) {
                        $textnode->setMetadata($metadata);
                    }

                    $endPos += strlen("]]");
                    $startPos = strpos($textnodeText, "[[", $endPos);

                    if ($startPos !== false) {
                        $textnodeTextNew .= substr($textnodeText, $endPos, $startPos - $endPos);
                    } else {
                        $textnodeTextNew .= substr($textnodeText, $endPos);
                    }
                }
            } else {
                $textnodeTextNew = $textnodeText;
            }

            $textnodeText = $textnodeTextNew;
            $textnodeTextLength = strlen($textnodeText);
            $textnodeTextNew = "<p>";
            $consumed = 0;

            for ($i = 0; $i < $textnodeTextLength; $i++) {
                if ($textnodeText[$i] == "\n" || $textnodeText[$i] == "\r") {
                    $consumed++;

                    continue;
                }
                if ($consumed > 0 && $i > $consumed) {
                    $textnodeTextNew .= "</p><p>";
                }

                $textnodeTextNew .= $textnodeText[$i];
                $consumed = 0;
            }

            $textnodeTextNew .= "</p>";

            $textnode->setText($textnodeTextNew);
        }

        /** @todo Check if there are textnodes which can't be reached. */

        $this->twineRelevant = false;
        $this->twineStartnodeId = -1;
        $this->textnodeMapping = null;
        $this->accessSet = false;
    }

    private function endElementPassageData()
    {
        $this->dm->persist($this->textnode);

        $this->textnodeMapping[$this->twineTextnodeName] = $this->textnode->getId();
        $this->twineTextnodeName = null;

        $this->twineText = false;
        $this->textnode = null;
    }

    private function endElement($parser, $name)
    {
        if ($name === "tw-storydata") {
            $this->endElementStoryData($name);
        } elseif ($this->twineRelevant === true && $name === "tw-passagedata") {
            $this->endElementPassageData();
        }
    }
}