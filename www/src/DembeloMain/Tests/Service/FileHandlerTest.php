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

namespace DembeloMain\Tests\Service;

use DembeloMain\Service\FileHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class FileHandlerTest
 * @package DembeloMain\Tests\Service
 */
class FileHandlerTest extends TestCase
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var string
     */
    private $tmpFileName;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->fileHandler = new FileHandler();
        $this->tmpFileName = @tempnam('/tmp/phpunit', 'fileHandlerTest');
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->fileHandler = null;
        if (file_exists($this->tmpFileName)) {
            unlink($this->tmpFileName);
        }
    }

    /**
     * @return void
     */
    public function testOpenThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->fileHandler->open('unknownFile', 'r');
    }

    /**
     * @return void
     */
    public function testOpenReturnsNewObjectWhenFileExists(): void
    {
        touch($this->tmpFileName);
        $newObject = $this->fileHandler->open($this->tmpFileName, 'r');
        self::assertInstanceOf(FileHandler::class, $newObject);
        self::assertNotSame($this->fileHandler, $newObject);
    }

    /**
     * @return void
     */
    public function testCloseWhenNotInitialized(): void
    {
        self::assertFalse($this->fileHandler->close());
    }

    /**
     * @return void
     */
    public function testCloseWhenInitialized(): void
    {
        touch($this->tmpFileName);
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r');
        self::assertTrue($fileHandler->close());
    }

    /**
     * @return void
     */
    public function testEofOnEmptyFile(): void
    {
        touch($this->tmpFileName);
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r');
        $fileHandler->read(1);
        self::assertTrue($fileHandler->eof());
    }

    /**
     * @return void
     */
    public function testEofForNotEmptyFile(): void
    {
        file_put_contents($this->tmpFileName, 'one');
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r');
        $fileHandler->read(2);
        self::assertFalse($fileHandler->eof());
        $fileHandler->read(2);
        self::assertTrue($fileHandler->eof());
    }

    /**
     * @return void
     */
    public function testRead(): void
    {
        file_put_contents($this->tmpFileName, 'onetwothree');
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r');
        self::assertEquals('one', $fileHandler->read(3));
        self::assertEquals('two', $fileHandler->read(3));
        self::assertEquals('three', $fileHandler->read(6));
        self::assertEquals('', $fileHandler->read(1));

    }

    /**
     * @return void
     */
    public function testSeek(): void
    {
        file_put_contents($this->tmpFileName, 'onetwothree');
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r');
        $fileHandler->seek(3);
        self::assertEquals('two', $fileHandler->read(3));
        $fileHandler->seek(0);
        self::assertEquals('one', $fileHandler->read(3));
    }

    /**
     * @return void
     */
    public function testWrite(): void
    {
        file_put_contents($this->tmpFileName, 'one');
        $fileHandler = $this->fileHandler->open($this->tmpFileName, 'r+');
        self::assertEquals('one', $fileHandler->read(3));
        $fileHandler->write('two');
        $fileHandler->close();
        self::assertStringEqualsFile($this->tmpFileName, 'onetwo');
    }
}