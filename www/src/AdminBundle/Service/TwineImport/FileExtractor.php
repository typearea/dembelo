<?php
/* Copyright (C) 2017 Michael Giesler
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

/**
 * Class FileExtractor
 * @package AdminBundle\Service\TwineImport
 */
class FileExtractor
{
    /**
     * @param string $filename
     * @return string
     */
    public function extract(string $filename): string
    {
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException('Failed to read data from file ['.$filename.'].');
        }
        $extractedFilename = $filename.'.extracted';
        $fileHandle = fopen($filename, 'r');
        $extractedFileHandle = fopen($extractedFilename, 'w');
        $writing = false;
        $matches = [];

        while (($row = fgets($fileHandle)) !== false) {
            if ($writing) {
                if (preg_match('(^.*</tw-storydata>)', $row, $matches)) {
                    fwrite($extractedFileHandle, $matches[0]);
                    break;
                }
                fwrite($extractedFileHandle, $row);
            } else {
                if (preg_match('(<tw-storydata.*$)', $row, $matches)) {
                    fwrite($extractedFileHandle, $matches[0]);
                    $writing = true;
                }
            }
        }

        fclose($fileHandle);
        fclose($extractedFileHandle);

        return $extractedFilename;
    }
}
