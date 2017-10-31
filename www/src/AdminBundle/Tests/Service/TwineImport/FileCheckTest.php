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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileCheckTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testCheck(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fwrite($fileHandler, '<tw-storydata hurz');
        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $returnValue = $fileCheck->check($fileHandler, 'someFileName');
        self::assertTrue($returnValue);
        unlink($tmpName);
    }

    /**
     * @return void
     */
    public function testCheckWithLeadingEmptySpaces(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fwrite($fileHandler, '  ' . "\n" . '<tw-storydata hurz');
        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $returnValue = $fileCheck->check($fileHandler, 'someFileName');
        self::assertTrue($returnValue);
        unlink($tmpName);
    }

    /**
     * @return void
     * @expectedException \Exception
     */
    public function testCheckWithTooShortPeekData(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fwrite($fileHandler, '<tw-storyda');
        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $fileCheck->check($fileHandler, 'someFileName');
        unlink($tmpName);
    }

    /**
     * @return void
     * @expectedException \Exception
     */
    public function testCheckWithWrongPeekData(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fwrite($fileHandler, '<tw-storrydata ');
        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $fileCheck->check($fileHandler, 'someFileName');
        unlink($tmpName);
    }

    /**
     * @return void
     * @expectedException \Exception
     */
    public function testCheckWithEmptyPeekData(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $fileCheck->check($fileHandler, 'someFileName');
        unlink($tmpName);
    }

    /**
     * @return void
     * @expectedException \Exception
     */
    public function testCheckWithOnlyWhitespacePeekData(): void
    {
        $tmpName = @tempnam('/tmp/phpunit', 'filecheck');
        $fileHandler = fopen($tmpName, 'r+');
        fwrite($fileHandler, ' ');

        fseek($fileHandler, 0);

        $fileCheck = new FileCheck();
        $fileCheck->check($fileHandler, 'someFileName');
        unlink($tmpName);
    }
}
