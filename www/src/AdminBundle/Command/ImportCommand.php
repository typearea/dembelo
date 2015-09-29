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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DembeloMain\Document\Textnode;

/**
 * Class ImportCommand
 * @package AdminBundle
 */
class ImportCommand extends ContainerAwareCommand
{
    protected $twineArchivePath;
    protected $output = null;
    protected $mongo = null;
    protected $dm = null;
    protected $repositoryTopic = null;
    protected $textnode = null;
    protected $topicId = null;
    protected $twineRelevant = false;
    protected $twineStartnodeId = -1;
    protected $twineText = false;
    protected $accessSet = false;


    protected function configure()
    {
        $this
            ->setName('dembelo:import')
            ->setDescription('Twine Archive Import')
            ->addArgument(
                'twine-archive-file',
                InputArgument::REQUIRED,
                'The path of the Twine archive file.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $styleWarning = new OutputFormatterStyle('black', 'yellow');
        $output->getFormatter()->setStyle('warning', $styleWarning);

        $this->twineArchivePath = $input->getArgument('twine-archive-file');

        if (file_exists($this->twineArchivePath) !== true) {
            $output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' doesn't exist.</error>");

            return -1;
        }

        if (is_readable($this->twineArchivePath) !== true) {
            $output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' isn't readable.</error>");

            return -1;
        }

        $twineArchiveFile = @fopen($this->twineArchivePath, "rb");

        if ($twineArchiveFile === false) {
            $output->writeln("<error>Couldn't open file '".$this->twineArchivePath."'.</error>");

            return -1;
        }

        try {
            $magicString = "<tw-storydata ";
            $magicStringLength = strlen($magicString);
            $peekData = @fread($twineArchiveFile, 1024);

            if ($peekData === false) {
                throw new \Exception("<error>Failed to read data from file '".$this->twineArchivePath."'.</error>");
            }

            $peekDataLength = strlen($peekData);

            if ($peekDataLength <= 0) {
                throw new \Exception("<warning>File '".$this->twineArchivePath."' seems to be empty.</warning>");
            }

            $magicStringFound = false;

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

                $magicStringFound = true;

                break;
            }

            if ($magicStringFound != true) {
                throw new \Exception("<error>File '".$this->twineArchivePath."' doesn't seem to be a Twine archive file.</error>");
            }

            if (@fseek($twineArchiveFile, 0) !== 0) {
                throw new \Exception("<error>Couldn't reset reading position after the magic string in the Twine archive file '".$this->twineArchivePath."' was checked.</error>");
            }

            $this->output = $output;
            $xmlParser = xml_parser_create("UTF-8");
            xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, 0);

            if (xml_set_element_handler($xmlParser, array(&$this, "startElement"), array(&$this, "endElement")) !== true) {
                xml_parser_free($xmlParser);
                throw new \Exception("<error>Couldn't register start/end event handlers for the XML parser.</error>");
            }

            if (xml_set_character_data_handler($xmlParser, array(&$this, "characterData")) !== true) {
                xml_parser_free($xmlParser);
                throw new \Exception("<error>Couldn't register character data event handler for the XML parser.</error>");
            }

            $this->mongo = $this->getContainer()->get('doctrine_mongodb');
            $this->dm = $this->mongo->getManager();
            $this->repositoryTopic = $this->mongo->getRepository('DembeloMain:Topic');

            /** @todo This should be part of the Twine export. */
            if (xml_parse($xmlParser, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tw-archive>\n", false) !== 1) {
                $errorCode = xml_get_error_code($xmlParser);
                $errorDescription = xml_error_string($errorCode);

                xml_parser_free($xmlParser);
                throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while the envelope head for the Twine archive was parsed.</error>");
            }

            do {
                $buffer = @fread($twineArchiveFile, 4096);

                if ($buffer === false) {
                    break;
                }

                if (xml_parse($xmlParser, $buffer, false) !== 1) {
                    $errorCode = xml_get_error_code($xmlParser);
                    $errorDescription = xml_error_string($errorCode);
                    $errorRowNumber = xml_get_current_line_number($xmlParser);
                    $errorColumnNumber = xml_get_current_column_number($xmlParser);
                    $errorByteIndex = xml_get_current_byte_index($xmlParser);

                    xml_parser_free($xmlParser);
                    throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while parsing the Twine archive file '".$this->twineArchivePath."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").</error>");
                }

            } while (@feof($twineArchiveFile) === false);

            if (xml_parse($xmlParser, "\n</tw-archive>\n", true) !== 1) {
                $errorCode = xml_get_error_code($xmlParser);
                $errorDescription = xml_error_string($errorCode);

                xml_parser_free($xmlParser);
                throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while the envelope foot for the Twine archive was parsed.</error>");
            }

            xml_parser_free($xmlParser);
            $this->output = null;

            // PHP 5.4 compatibility: there's no 'finally' yet.
            if (@fclose($twineArchiveFile) === false) {
                $output->writeln("<warning>Couldn't close file '".$this->twineArchivePath."'.</warning>");

                return 1;
            }

            $this->dm->flush();

        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());

            if (@fclose($twineArchiveFile) === false) {
                $output->writeln("<warning>Couldn't close file '".$this->twineArchivePath."'.</warning>");
            }

            $this->output = null;

            return -1;
        }

        return 0;
    }

    private function startElement($parser, $name, $attrs)
    {
        if ($name === "tw-storydata") {
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

            $twineStoryName = explode("->", $attrs['name'], 2);

            if (count($twineStoryName) !== 2) {
                throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which has an incomplete 'name' attribute. Twine stories must use the naming schema '?->story name', where '?' is an existing Dembelo Topic Id. Instead, '".$attrs['name']."' was found.</error>");
            }

            $topic = $this->repositoryTopic->createQueryBuilder()
                ->field('id')->equals(new \MongoId($twineStoryName[0]))
                ->getQuery()->getSingleResult();

            if (is_null($topic)) {
                throw new \Exception("<error>The Dembelo Topic with Id '".$twineStoryName[0]."', referenced by Twine story '".$attrs['name']."' in the Twine archive file '".$this->twineArchivePath."', doesn't exist.</error>");
            }

            $this->twineStartnodeId = $attrs['startnode'];
            $this->topicId = $twineStoryName[0];
            $this->twineRelevant = true;

        } elseif ($this->twineRelevant === true && $name === "tw-passagedata") {
            if ($this->twineText !== false) {
                throw new \Exception("<error>Nested '".$name."' found in Twine archive file '".$this->twineArchivePath."'.</error>");
            }

            if (isset($attrs['pid']) !== true) {
                throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which is missing its 'pid' attribute.</error>");
            }

            if (is_numeric($attrs['pid']) !== true) {
                throw new \Exception("<error>There is a '".$name."' in the Twine archive file '".$this->twineArchivePath."' which hasn't a numeric value in its 'pid' attribute ('".$attrs['pid']."' was found instead).</error>");
            }

            $this->textnode = new Textnode();
            $this->textnode->setStatus(Textnode::STATUS_ACTIVE);
            $this->textnode->setCreated(date('Y-m-d H:i:s'));
            $this->textnode->setTopicId($this->topicId);

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
    }

    private function characterData($parser, $data)
    {
        if ($this->twineRelevant === true && $this->twineText === true) {
            $this->textnode->setText($this->textnode->getText().$data);
        }
    }

    private function endElement($parser, $name)
    {
        if ($name === "tw-storydata") {
            $this->twineRelevant = false;
            $this->twineStartnodeId = -1;
            $this->accessSet = false;
        } elseif ($this->twineRelevant === true && $name === "tw-passagedata") {
            $this->dm->persist($this->textnode);

            $this->output->writeln("Created Dembelo Textnode with Id '".$this->textnode->getId()."'.");

            $this->twineText = false;
            $this->textnode = null;
        }
    }
}
