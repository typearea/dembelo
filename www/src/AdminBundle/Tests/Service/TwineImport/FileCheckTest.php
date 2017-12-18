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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class FileCheckTest
 */
class FileCheckTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileHandler
     */
    private $fileHandlerMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->fileHandlerMock = $this->createMock(FileHandler::class);
    }

    /**
     * @return void php
     *
     * @throws \Exception
     */
    public function testCheck(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn('<tw-storydata hurz');

        $fileCheck = new FileCheck();
        $returnValue = $fileCheck->check($this->fileHandlerMock, 'someFileName');
        self::assertTrue($returnValue);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testCheckWithLeadingEmptySpaces(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn('  '."\n".'<tw-storydata hurz');

        $fileCheck = new FileCheck();
        $returnValue = $fileCheck->check($this->fileHandlerMock, 'someFileName');
        self::assertTrue($returnValue);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @throws \Exception
     */
    public function testCheckWithTooShortPeekData(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn('<tw-storyda');

        $fileCheck = new FileCheck();
        $fileCheck->check($this->fileHandlerMock, 'someFileName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @throws \Exception
     */
    public function testCheckWithWrongPeekData(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn('<tw-storrydata ');

        $fileCheck = new FileCheck();
        $fileCheck->check($this->fileHandlerMock, 'someFileName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @throws \Exception
     */
    public function testCheckWithEmptyPeekData(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn('');

        $fileCheck = new FileCheck();
        $fileCheck->check($this->fileHandlerMock, 'someFileName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @throws \Exception
     */
    public function testCheckWithOnlyWhitespacePeekData(): void
    {
        $this->fileHandlerMock->method('read')
            ->willReturn(' ');

        $fileCheck = new FileCheck();
        $fileCheck->check($this->fileHandlerMock, 'someFileName');
    }
}
