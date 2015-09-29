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
 * @package DembeloMain
 */

namespace DembeloMain\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DembeloMain\Document\Textnode;

/**
 * Class ImportCommand
 * @package DembeloMain
 */
class ImportCommand extends ContainerAwareCommand
{
    protected $output;
    protected $textnode;

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

        $twineArchivePath = $input->getArgument('twine-archive-file');

        if (file_exists($twineArchivePath) !== true) {
            $output->writeln("<error>Parameter 'twine-archive-file': File '".$twineArchivePath."' doesn't exist.</error>");

            return -1;
        }

        if (is_readable($twineArchivePath) !== true) {
            $output->writeln("<error>Parameter 'twine-archive-file': File '".$twineArchivePath."' isn't readable.</error>");

            return -1;
        }

        $twineArchiveFile = @fopen($twineArchivePath, "rb");

        if ($twineArchiveFile === false) {
            $output->writeln("<error>Couldn't open file '".$twineArchivePath."'.</error>");

            return -1;
        }

        try {
            $magicString = "<tw-storydata ";
            $magicStringLength = strlen($magicString);
            $peekData = @fread($twineArchiveFile, 1024);

            if ($peekData === false) {
                throw new \Exception("<error>Failed to read data from file '".$twineArchivePath."'.</error>");
            }

            $peekDataLength = strlen($peekData);

            if ($peekDataLength <= 0) {
                throw new \Exception("<warning>File '".$twineArchivePath."' seems to be empty.</warning>");
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
                    throw new \Exception("<error>File '".$twineArchivePath."' isn't a Twine archive file.</error>");
                }

                if (substr($peekData, $i, $magicStringLength) !== $magicString) {
                    throw new \Exception("<error>File '".$twineArchivePath."' isn't a Twine archive file.</error>");
                }

                $magicStringFound = true;

                break;
            }

            if ($magicStringFound != true) {
                throw new \Exception("<error>File '".$twineArchivePath."' doesn't seem to be a Twine archive file.</error>");
            }

            if (@fseek($twineArchiveFile, 0) !== 0) {
                throw new \Exception("<error>Couldn't reset reading position after the magic string in the Twine archive file '".$twineArchivePath."' was checked.</error>");
            }


            $this->output = $output;
            $xmlParser = xml_parser_create("UTF-8");
            xml_parser_set_option($xmlParser, XML_OPTION_CASE_FOLDING, 0);

            if (xml_set_element_handler($xmlParser, array(&$this, "startElement"), array(&$this, "endElement")) !== true) {
                xml_parser_free($xmlParser);
                throw new \Exception("<error>Couldn't register event handlers for the XML parser.</error>");
            }

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
                    throw new \Exception("<error>Error #".$errorCode.": '".$errorDescription."' occurred while parsing the Twine archive file '".$twineArchivePath."' in line ".$errorRowNumber.", character ".$errorColumnNumber." (at byte index ".$errorByteIndex.").</error>");
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
                $output->writeln("<warning>Couldn't close file '".$twineArchivePath."'.</warning>");

                return 1;
            }

        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());

            if (@fclose($twineArchiveFile) === false) {
                $output->writeln("<warning>Couldn't close file '".$twineArchivePath."'.</warning>");
            }

            $this->output = null;

            return -1;
        }

        return 0;
    }

    private function startElement($parser, $name, $attrs)
    {
        if ($this->output !== null) {
            $this->output->writeln($name);
        }

        if ($name === "tw-storydata") {
            $this->textnode = new Textnode();
        }
    }

    private function endElement($parser, $name)
    {
        if ($name === "tw-storydata") {
            $this->textnode = null;
        }
    }
}
