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
 * Class FileCheck
 * @package AdminBundle\Service\TwineImport
 */
class FileCheck
{
    private const OPENING_STRING = '<tw-storydata ';

    /**
     * @param $fileHandler
     * @param $filename
     * @return bool
     * @throws \Exception
     */
    public function check($fileHandler, $filename): bool
    {
        $magicStringLength = strlen(self::OPENING_STRING);

        $peekData = fread($fileHandler, 1024);

        if ($peekData === false) {
            throw new \Exception("Failed to read data from file '".$filename."'.");
        }

        $peekDataLength = strlen($peekData);

        if ($peekDataLength <= 0) {
            throw new \Exception("File '".$filename."' seems to be empty.");
        }

        for ($i = 0; $i < $peekDataLength; $i++) {
            if ($peekData[$i] === ' ' ||
                $peekData[$i] === "\n" ||
                $peekData[$i] === "\r" ||
                $peekData[$i] === "\t") {
                // Consume whitespace.
                continue;
            }

            if ($peekDataLength - $i < $magicStringLength) {
                throw new \Exception("File '".$filename."' isn't a Twine archive file.");
            }

            if (substr($peekData, $i, $magicStringLength) !== self::OPENING_STRING) {
                throw new \Exception("File '".$filename."' isn't a Twine archive file.");
            }

            if (fseek($fileHandler, 0) !== 0) {
                throw new \Exception("Couldn't reset reading position after the magic string in the Twine archive file '".$filename."' was checked.");
            }

            return true;
        }

        throw new \Exception("File '".$filename."' doesn't seem to be a Twine archive file.");
    }
}