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
namespace AdminBundle\Tests\Service\TwineImport;

use AdminBundle\Service\TwineImport\ImportTwine;
use AdminBundle\Service\TwineImport\FileCheck;
use AdminBundle\Service\TwineImport\FileExtractor;
use AdminBundle\Service\TwineImport\ParserContext;
use AdminBundle\Service\TwineImport\PassageDataParser;
use AdminBundle\Service\TwineImport\StoryDataParser;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Service\FileHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class ImportTwineTest
 */
class ImportTwineTest extends WebTestCase
{
    /**
     * @var ImportTwine
     */
    private $importTwine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileExtractor
     */
    private $fileExtractorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileCheck
     */
    private $fileCheckMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoryDataParser
     */
    private $storyDataParserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PassageDataParser
     */
    private $passageDataParserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ParserContext
     */
    private $parserContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileHandler
     */
    private $fileHandlerMock;

    /**
     * resets some variables
     */
    public function setUp()
    {
        $this->fileExtractorMock = $this->createFileExtractorMock();
        $this->fileCheckMock = $this->createFileCheckMock();
        $this->storyDataParserMock = $this->createStoryDataParserMock();
        $this->passageDataParserMock = $this->createPassageDataParserMock();
        $this->parserContextMock = $this->createParserContextMock();
        $this->fileHandlerMock = $this->createFileHandlerMock();

        $this->importTwine = new ImportTwine(
            $this->fileExtractorMock,
            $this->fileCheckMock,
            $this->storyDataParserMock,
            $this->passageDataParserMock,
            $this->parserContextMock,
            $this->fileHandlerMock
        );
    }

    /**
     * @return void
     */
    public function testRun(): void
    {
        $expectedStoryDataAttributes = [
            'attr1' => 'val1',
            'attr2' => 'val2',
        ];
        $expectedPassageDataAttributes = [
            'attr3' => 'val3',
            'attr4' => 'val4',
        ];
        $this->fileHandlerMock->expects(self::once())
            ->method('open')
            ->willReturnSelf();
        $this->fileHandlerMock->expects(self::any())
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                '<tw-storydata attr1="val1" attr2="val2">',
                '<tw-passagedata attr3="val3" attr4="val4">',
                '</tw-passagedata>',
                '</tw-storydata>'
            );
        $this->fileHandlerMock->expects(self::any())
            ->method('eof')
            ->willReturnOnConsecutiveCalls(
                false, false, false, true
            );
        $importFileMock = $this->createImportFileMock();

        $textnodeMock = $this->createTextnodeMock();

        $this->parserContextMock->expects(self::any())
            ->method('isTwineRelevant')
            ->willReturn(true);
        $this->parserContextMock->expects(self::any())
            ->method('isTwineText')
            ->willReturn(true);
        $this->parserContextMock->expects(self::any())
            ->method('getCurrentTextnode')
            ->willReturn($textnodeMock);

        $this->fileExtractorMock->expects(self::once())
            ->method('extract')
            ->willReturn('readablefilename');

        $this->fileCheckMock->expects(self::once())
            ->method('check');

        $this->storyDataParserMock->expects(self::once())
            ->method('startElement')
            ->with('tw-storydata', $expectedStoryDataAttributes);

        $this->passageDataParserMock->expects(self::once())
            ->method('startElement')
            ->with('tw-passagedata', $expectedPassageDataAttributes);

        $this->importTwine->run($importFileMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FileExtractor
     */
    private function createFileExtractorMock(): FileExtractor
    {
        return $this->createMock(FileExtractor::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FileCheck
     */
    private function createFileCheckMock(): FileCheck
    {
        return $this->createMock(FileCheck::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StoryDataParser
     */
    private function createStoryDataParserMock(): StoryDataParser
    {
        return $this->createMock(StoryDataParser::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PassageDataParser
     */
    private function createPassageDataParserMock(): PassageDataParser
    {
        return $this->createMock(PassageDataParser::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ParserContext
     */
    private function createParserContextMock():ParserContext
    {
        $mock = $this->createMock(ParserContext::class);
        $mock->expects(self::any())
            ->method('getFilename')
            ->willReturn('someFilename');

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Importfile
     */
    private function createImportFileMock(): Importfile
    {
        return $this->createMock(Importfile::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Textnode
     */
    private function createTextnodeMock(): Textnode
    {
        return $this->createMock(Textnode::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FileHandler
     */
    private function createFileHandlerMock(): FileHandler
    {
        return $this->createMock(FileHandler::class);
    }
}
