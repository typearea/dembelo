<?php
/* Copyright (C) 2015 Stephan Kreutzer
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

namespace AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DembeloMain\Document\Textnode;

/**
 * Class ImportCommand
 * @package AdminBundle
 */
class ImportCommand extends ContainerAwareCommand
{
    private $twineArchivePath;

    /**
     * @var OutputInterface
     */
    private $output = null;
    private $mongo = null;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm = null;
    private $repositoryTopic = null;

    private $licensee = null;
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


    /**
     * configures the symfony cli command
     */
    protected function configure()
    {
        $this
            ->setName('dembelo:import')
            ->setDescription('Twine Archive Import')
            ->addArgument(
                'twine-archive-file',
                InputArgument::REQUIRED,
                'The path of the Twine archive file.'
            )
            ->addOption(
                'licensee-name',
                'l',
                InputOption::VALUE_REQUIRED,
                'The name of the licensee to which the imported textnodes belong to.'
            )
            ->addOption(
                'metadata-author',
                'a',
                InputOption::VALUE_REQUIRED,
                'The author of all the stories in the Twine archive file (will end up as metadata).'
            )
            ->addOption(
                'metadata-publisher',
                'p',
                InputOption::VALUE_REQUIRED,
                'The publisher of all the stories in the Twine archive file (will end up as metadata).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->prepare($input);

        if (file_exists($this->twineArchivePath) !== true) {
            $this->output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' doesn't exist.</error>");

            return -1;
        }

        if (is_readable($this->twineArchivePath) !== true) {
            $this->output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' isn't readable.</error>");

            return -1;
        }

        $this->extractTwineFile($this->twineArchivePath);

        $fileHandler = fopen($this->twineArchivePath.'.extracted', "rb");

        if ($fileHandler === false) {
            $this->output->writeln("<error>Couldn't open file '".$this->twineArchivePath."'.</error>");

            return -1;
        }

        $xmlParser = xml_parser_create("UTF-8");

        try {
            $this->checkTwineFile($fileHandler);

            if (!$this->initParser($xmlParser, $fileHandler)) {
                return 1;
            }

            $this->dm->flush();

        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());

            xml_parser_free($xmlParser);

            if (fclose($fileHandler) === false) {
                $this->output->writeln("<warning>Couldn't close file '".$this->twineArchivePath."'.</warning>");
            }

            return -1;
        }

        return 0;
    }

    private function extractTwineFile($file)
    {
        $extractedFile = $file.'.extracted';

        $fileHandle = fopen($file, 'r');
        $extractedFileHandle = fopen($extractedFile, 'w');
        if ($fileHandle === false) {
            throw new \Exception("<error>Failed to read data from file '".$this->twineArchivePath."'.</error>");
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

    }

    private function checkTwineFile($fileHandler)
    {
        $magicString = "<tw-storydata ";
        $magicStringLength = strlen($magicString);

        $peekData = fread($fileHandler, 1024);

        if ($peekData === false) {
            throw new \Exception("<error>Failed to read data from file '".$this->twineArchivePath."'.</error>");
        }

        $peekDataLength = strlen($peekData);

        if ($peekDataLength <= 0) {
            throw new \Exception("<warning>File '".$this->twineArchivePath."' seems to be empty.</warning>");
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
                throw new \Exception("<error>File '".$this->twineArchivePath."' isn't a Twine archive file.</error>");
            }

            if (substr($peekData, $i, $magicStringLength) !== $magicString) {
                throw new \Exception("<error>File '".$this->twineArchivePath."' isn't a Twine archive file.</error>");
            }

            if (fseek($fileHandler, 0) !== 0) {
                throw new \Exception("<error>Couldn't reset reading position after the magic string in the Twine archive file '".$this->twineArchivePath."' was checked.</error>");
            }

            return true;

        }

        throw new \Exception("<error>File '".$this->twineArchivePath."' doesn't seem to be a Twine archive file.</error>");
    }

    private function prepare(InputInterface $input)
    {
        $styleWarning = new OutputFormatterStyle('black', 'yellow');
        $this->output->getFormatter()->setStyle('warning', $styleWarning);

        $this->mongo = $this->getContainer()->get('doctrine_mongodb');
        $this->dm = $this->mongo->getManager();

        $repositoryLicensee = $this->mongo->getRepository('DembeloMain:Licensee');

        /**
         * @var $licensee \DembeloMain\Document\Licensee
         */
        $licensee = $repositoryLicensee->findOneByName($input->getOption('licensee-name'));
        if (is_null($licensee)) {
            throw new \Exception("<error>A Licensee named '".$input->getOption('licensee-name')."' doesn't exist.</error>");
        }

        $this->author = $input->getOption('metadata-author');
        $this->publisher = $input->getOption('metadata-publisher');

        $this->licensee = $licensee->getId();

        $this->twineArchivePath = $input->getArgument('twine-archive-file');

    }

    private function initParser($xmlParser, $fileHandler)
    {
        xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, 0);

        if (xml_set_element_handler($xmlParser, array($this, "startElement"), array($this, "endElement")) !== true) {
            throw new \Exception("<error>Couldn't register start/end event handlers for the XML parser.</error>");
        }

        if (xml_set_character_data_handler($xmlParser, array($this, "characterData")) !== true) {
            throw new \Exception("<error>Couldn't register character data event handler for the XML parser.</error>");
        }

        $this->repositoryTopic = $this->mongo->getRepository('DembeloMain:Topic');

        /** @todo This should be part of the Twine export. */
        if (xml_parse($xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n", false) !== 1) {
            $errorCode = xml_get_error_code($xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while the envelope head for the Twine archive was parsed.</error>");
        }

        do {
            $buffer = fread($fileHandler, 4096);

            if ($buffer === false) {
                break;
            }

            if (xml_parse($xmlParser, $buffer, false) !== 1) {
                $errorCode = xml_get_error_code($xmlParser);
                $errorDescription = xml_error_string($errorCode);
                $errorRowNumber = xml_get_current_line_number($xmlParser);
                $errorColumnNumber = xml_get_current_column_number($xmlParser);
                $errorByteIndex = xml_get_current_byte_index($xmlParser);

                throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while parsing the Twine archive file '".$this->twineArchivePath."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").</error>");
            }

        } while (feof($fileHandler) === false);

        if (xml_parse($xmlParser, "\n</tw-archive>\n", true) !== 1) {
            $errorCode = xml_get_error_code($xmlParser);
            $errorDescription = xml_error_string($errorCode);

            throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while the envelope foot for the Twine archive was parsed.</error>");
        }

        xml_parser_free($xmlParser);
        $this->output = null;

        // PHP 5.4 compatibility: there's no 'finally' yet.
        if (fclose($fileHandler) === false) {
            $this->output->writeln("<warning>Couldn't close file '".$this->twineArchivePath."'.</warning>");

            return false;
        }

        return true;
    }

    private function startElementStoryData($name, $attrs)
    {
        if ($this->twineRelevant === true) {
            throw new \Exception("<error>Nested '".$name."' found in Twine archive file '".$this->twineArchivePath."'.</error>");
        }

        if (isset($attrs['startnode']) !== true) {
            $this->output->writeln("<warning>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which doesn't specify its startnode. Import of this '".$name."' skipped.</warning>");

            return;
        }

        if (is_numeric($attrs['startnode']) !== true) {
            $this->output->writeln("<warning>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which hasn't a numeric value in its 'startnode' attribute ('".$attrs['startnode']."' was found instead). Import of this '".$name."' skipped.</warning>");

            return;
        }

        if (isset($attrs['name']) !== true) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'name' attribute.</error>");
        }

        $twineStory = explode("-->", $attrs['name'], 2);

        if (count($twineStory) !== 2) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which has an incomplete 'name' attribute. Twine stories must use the naming schema '?-->story name', where '?' is an existing Dembelo Topic Id. Instead, '".$attrs['name']."' was found.</error>");
        }

        $this->topicId = $twineStory[0];

        $topic = $this->repositoryTopic->find($this->topicId);

        if (is_null($topic)) {
            throw new \Exception("<error>The Dembelo Topic with Id '".$this->topicId."', referenced by Twine story '".$attrs['name']."' in the Twine archive file '".$this->twineArchivePath."', doesn't exist.</error>");
        }

        $this->twineStartnodeId = $attrs['startnode'];
        $this->textnodeMapping = array();
        $this->twineRelevant = true;
    }

    private function startElementPassageData($name, $attrs)
    {
        if ($this->twineText !== false) {
            throw new \Exception("<error>Nested '".$name."' found in Twine archive file '".$this->twineArchivePath."'.</error>");
        }

        if (isset($attrs['pid']) !== true) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'pid' attribute.</error>");
        }

        if (is_numeric($attrs['pid']) !== true) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which hasn't a numeric value in its 'pid' attribute ('".$attrs['pid']."' was found instead).</error>");
        }

        if (isset($attrs['name']) !== true) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'name' attribute.</error>");
        }

        if (array_key_exists($attrs['name'], $this->textnodeMapping) === true) {
            throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which has a 'name' attribute with value '".$attrs['name']."'. This value is used more than once, while it must be unique.</error>");
        }

        $this->twineTextnodeName = $attrs['name'];

        $this->textnode = new Textnode();
        $this->textnode->setStatus(Textnode::STATUS_ACTIVE);
        $this->textnode->setCreated(date('Y-m-d H:i:s'));
        $this->textnode->setTopicId($this->topicId);
        $this->textnode->setLicenseeId($this->licensee);
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
                throw new \Exception("<error>There is more than one '".$name."' in the Twine archive file '".$this->twineArchivePath."' with the startnode value '".$attrs['pid']."' in its 'pid' attribute.</error>");
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
                throw new \Exception("<error>The Dembelo Textnode with Id '".$dembeloId."' doesn't exist, but should by now.</error>");
            }

            /** @todo The links should be exported as XML as well instead of a custom Twine inline format. */

            $textnodeText = $textnode->getText();
            $startPos = strpos($textnodeText, "[[", 0);

            if ($startPos !== false) {
                $textnodeTextNew = substr($textnodeText, 0, $startPos);

                while ($startPos !== false) {
                    $endPos = strpos($textnodeText, "]]", $startPos + strlen("[["));

                    if ($endPos === false) {
                        throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' has a textnode named '".$twineName."' which contains a malformed link that starts with '[[' but has no corresponding ']]'.</error>");
                    }

                    $content = substr($textnodeText, $startPos + strlen("[["), $endPos - ($startPos + strlen("[[")));
                    $hitch = null;
                    $metadata = null;

                    if (strpos($content, "-->") !== false) {
                        $content = explode("-->", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."-->".$content[1]."]]'.</error>");
                        }

                        $repositoryTextnode = $this->mongo->getRepository('DembeloMain:Textnode');

                        $externalTextnode = $repositoryTextnode->createQueryBuilder()
                            ->field('id')->equals(new \MongoId($content[1]))
                            ->getQuery()->getSingleResult();

                        if (is_null($externalTextnode)) {
                            throw new \Exception("<error>There is a textnode named '".$twineName."' in the Twine archive file '".$this->twineArchivePath."' which references the external Dembelo Textnode '".$content[1]."', but a Dembelo Textnode with such an Id doesn't exist.</error>");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[0];
                        $hitch['textnodeId'] = $content[1];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, "->") !== false) {
                        $content = explode("->", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."->".$content[1]."]]'.</error>");
                        }

                        if (array_key_exists($content[1], $this->textnodeMapping) !== true) {
                            throw new \Exception("<error>There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content[1]."', but this textnode doesn't exist within the same story.</error>");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[0];
                        $hitch['textnodeId'] = $this->textnodeMapping[$content[1]];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, "<-") !== false) {
                        $content = explode("<-", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0]."<-".$content[1]."]]'.</error>");
                        }

                        if (array_key_exists($content[0], $this->textnodeMapping) !== true) {
                            throw new \Exception("<error>There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content[0]."', but this textnode doesn't exist within the same story.</error>");
                        }

                        $hitch = array();
                        $hitch['description'] = $content[1];
                        $hitch['textnodeId'] = $this->textnodeMapping[$content[0]];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    } elseif (strpos($content, ">:<") !== false) {
                        $content = explode(">:<", $content, 2);

                        if (strlen($content[0]) <= 0 || strlen($content[1]) <= 0) {
                            throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content[0].">:<".$content[1]."]]'.</error>");
                        }

                        $metadata = $textnode->getMetadata();

                        if (is_array($metadata) != true) {
                            $metadata = array();
                        }

                        if (array_key_exists($content[0], $metadata) === true) {
                            throw new \Exception("<error>There is a textnode in the Twine archive file '".$this->twineArchivePath."' which contains the metadata field '".$content[0]."' twice or would overwrite the already existing value of that field.</error>");
                        }

                        $metadata[$content[0]] = $content[1];
                    } else {
                        if (strlen($content) <= 0) {
                            throw new \Exception("<error>The Twine archive file '".$this->twineArchivePath."' contains a '".$name."' with the invalid element '[[".$content."]]'.</error>");
                        }

                        if (array_key_exists($content, $this->textnodeMapping) !== true) {
                            throw new \Exception("<error>There is a textnode in the Twine archive file '".$this->twineArchivePath."' which references another textnode named '".$content."', but this textnode doesn't exist within the same story.</error>");
                        }

                        $hitch = array();
                        $hitch['description'] = $content;
                        $hitch['textnodeId'] = $this->textnodeMapping[$content];
                        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
                    }

                    if ($hitch !== null) {
                        if ($textnode->getHitchCount() >= Textnode::HITCHES_MAXIMUM_COUNT) {
                            throw new \Exception("<error>There is a textnode named '".$twineName."' in the Twine archive file '".$this->twineArchivePath."' which has more than ".Textnode::HITCHES_MAXIMUM_COUNT." links.</error>");
                        }

                        if ($textnode->appendHitch($hitch) !== true) {
                            throw new \Exception("<error>Failed to append hitch for the textnode named '".$twineName."' from the Twine archive file '".$this->twineArchivePath."'.</error>");
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

    private function endElementPassageData($name)
    {
        $this->dm->persist($this->textnode);

        $this->textnodeMapping[$this->twineTextnodeName] = $this->textnode->getId();
        $this->twineTextnodeName = null;

        $this->output->writeln("Created Dembelo Textnode with Id '".$this->textnode->getId()."'.");

        $this->twineText = false;
        $this->textnode = null;
    }

    private function endElement($parser, $name)
    {
        if ($name === "tw-storydata") {
            $this->endElementStoryData($name);
        } elseif ($this->twineRelevant === true && $name === "tw-passagedata") {
            $this->endElementPassageData($name);
        }
    }
}
