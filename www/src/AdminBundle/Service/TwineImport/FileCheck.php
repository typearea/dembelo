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
use DembeloMain\Service\FileHandler;

/**
 * Class FileCheck
 */
class FileCheck
{
    /**
     * @var string
     */
    private const OPENING_STRING = '<tw-storydata ';

    /**
     * @param FileHandler $fileHandler
     * @param string   $filename
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function check(FileHandler $fileHandler, string $filename): bool
    {
        $magicStringLength = strlen(self::OPENING_STRING);

        $peekData = $fileHandler->read(1024);

        if (false === $peekData) {
            throw new \Exception(sprintf("Failed to read data from file '%s'.", $filename));
        }

        $peekDataLength = strlen($peekData);

        if ($peekDataLength <= 0) {
            throw new \Exception(sprintf("File '%s' seems to be empty.", $filename));
        }

        for ($i = 0; $i < $peekDataLength; ++$i) {
            if ($peekData[$i] === ' ' ||
                $peekData[$i] === "\n" ||
                $peekData[$i] === "\r" ||
                $peekData[$i] === "\t") {
                // Consume whitespace.
                continue;
            }

            if ($peekDataLength - $i < $magicStringLength) {
                throw new \Exception(sprintf("File '%s' isn't a Twine archive file.", $filename));
            }

            if (substr($peekData, $i, $magicStringLength) !== self::OPENING_STRING) {
                throw new \Exception(sprintf("File '%s' isn't a Twine archive file.", $filename));
            }

            if ($fileHandler->seek(0) !== 0) {
                throw new \Exception(sprintf("Couldn't reset reading position after the magic string in the Twine archive file '%s' was checked.", $filename));
            }

            return true;
        }

        throw new \Exception(sprintf("File '%s' doesn't seem to be a Twine archive file.", $filename));
    }
}
