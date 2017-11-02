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

/**
 * @package AdminBundle\Test
 */

// @codingStandardsIgnoreStart
namespace AdminBundle\Model;

use AdminBundle\Tests\Model\ImportTwineTest;

/**
 * mock function
 *
 * @param string $filename
 * @return bool
 */
function fopen($filename)
{
    return strpos($filename, 'readable') !== false;
}

/**
 * mock function
 */
function fclose(): bool
{
    return ImportTwineTest::$fcloseReturnValue;
}

/**
 * mock function
 *
 * @return bool|mixed
 */
function fread()
{
    if (empty(ImportTwineTest::$freadStack)) {
        return false;
    }

    return array_shift(ImportTwineTest::$freadStack);
}

/**
 * mock function
 *
 * @return bool
 */
function feof()
{
    return empty(ImportTwineTest::$freadStack);
}

/**
 * @param Resource $parser
 * @return void
 */
function xml_parser_free($parser)
{
    ImportTwineTest::$parserFreeCalled = true;
    \xml_parser_free($parser);
}

namespace AdminBundle\Tests\Model;

use AdminBundle\Model\ImportTwine;
use AdminBundle\Service\TwineImport\FileCheck;
use AdminBundle\Service\TwineImport\FileExtractor;
use AdminBundle\Service\TwineImport\HitchParser;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// @codingStandardsIgnoreEnd

/**
 * Class ImportTwineTest
 * @package AdminBundle\Tests\Model
 */
class ImportTwineTest extends WebTestCase
{

    public static $freadStack = [];
    public static $parserFreeCalled = false;
    public static $fcloseReturnValue = true;
    private $mocks;

    /**
     * @var ImportTwine
     */
    private $importTwine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HitchParser
     */
    private $hitchParserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileExtractor
     */
    private $fileExtractorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileCheck
     */
    private $fileCheckMock;

    /**
     * resets some variables
     */
    public function setUp()
    {
        self::$freadStack = [];
        $this->mocks = [];
        self::$parserFreeCalled = false;
        self::$fcloseReturnValue = true;

        $this->textnodeRepository = $this->getTextnodeRepositoryMock();
        $this->hitchParserMock = $this->createHitchParserMock();
        $this->fileExtractorMock = $this->createFileExtractorMock();
        $this->fileCheckMock = $this->createFileCheckMock();

        $this->importTwine = new ImportTwine(
            $this->textnodeRepository,
            $this->hitchParserMock,
            $this->fileExtractorMock,
            $this->fileCheckMock
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Couldn't open file/
     */
    public function testRunWithUnreadableFile()
    {
        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        $this->fileCheckMock->expects(self::never())
            ->method('check');

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('file.extracted');

        $this->importTwine->run($importfile);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File 'somefilename_readable' isn't a Twine archive file
     */
    public function testRunWithWrongFileFormat()
    {
        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        $this->fileCheckMock->expects(self::once())
            ->method('check')
            ->willThrowException(new \Exception('File \'somefilename_readable\' isn\'t a Twine archive file'));

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = ['erste Zeile', 'zweite Zeile'];

        $this->importTwine->run($importfile);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File 'somefilename_readable' seems to be empty.
     */
    public function testRunWithEmptyFirstLine()
    {
        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        $this->fileCheckMock->expects(self::once())
            ->method('check')
            ->willThrowException(new \Exception('Failed asserting that exception message \'File \'somefilename_readable\' isn\'t a Twine archive file\' contains \'File \'somefilename_readable\' seems to be empty.'));

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [''];

        $this->importTwine->run($importfile);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * tests the run method with correct but incomplete data
     *
     * @throws \Exception
     */
    public function testRunWithCorrectButIncompleteData()
    {
        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = ['zweite Zeile'];

        $retVal = $this->importTwine->run($importfile);
        $this->assertTrue($retVal);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * tests the run method when no textnode is written
     *
     * @throws \Exception
     */
    public function testRunButNoTextnodeIsWritten()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName" tags="Freigegeben ID:foobar" position="104,30">lorem impsum',
            'lorem impsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->willReturn($textnode);

        $retVal = $this->importTwine->run($importfile);
        $this->assertTrue($retVal);
        $this->textnodeRepository->expects($this->never())
            ->method('save');
    }

    /**
     * tests the run method with a single node containing text
     *
     * @throws \Exception
     */
    public function testRunWithSingleNodeWithText()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->willReturn($textnode);

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->willReturn($textnode);

        $this->textnodeRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($textnode) {
                return $textnode instanceof Textnode
                    && $textnode->getText() === "lorem ipsumlorem ipsum";
            }));

        $retVal = $this->importTwine->run($importfile);
        $this->assertTrue($retVal);
    }

    /**
     * check if exception is thrown when no licensee is available
     *
     * @expectedException Exception
     * @expectedExceptionMessage no licensee available
     */
    public function testRunWithExceptionWhenNoLicenseeIsAvailable()
    {
        $importfile = $this->getDummyImportfile(['licenseeId' => null]);

        $this->importTwine->run($importfile);
    }

    /**
     * check if exception is thrown when no licensee is available
     *
     * @expectedException Exception
     * @expectedExceptionMessage no filename available
     */
    public function testRunWithExceptionWhenNoFilenameIsAvailable()
    {
        $importfile = $this->getDummyImportfile(['filename' => null]);

        $this->importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage no ID given for Textnode "someNodeName1"
     */
    public function testRunWithTextnodeWithoutTwineID()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->never())
            ->method('save');

        $this->importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage no ID given for Textnode "someNodeName1"
     */
    public function testRunWithTextnodeWithoutAnyTag()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->never())
            ->method('save');

        $this->importTwine->run($importfile);
    }

    /**
     * tests identifying a textnode by a twine ID
     * @throws \Exception
     */
    public function testRunWithTextnodeWithTwineID()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar')
            ->willReturn($textnode);

        $this->importTwine->run($importfile);
    }

    /**
     * tests the freeing of xml parser
     * @throws \Exception
     */
    public function testParserFree()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar')
            ->willReturn($textnode);

        $this->importTwine->run($importfile);

        $this->assertTrue(self::$parserFreeCalled);
    }

    /**
     * tests disabling of a textnode that is no longer found in import file
     * @throws \Exception
     */
    public function testRunWithTextnodeDeletedInImportfile()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->once())
            ->method('disableOrphanedNodes')
            ->with($importfile, [$textnode->getId()]);

        $this->importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage There is a 'tw-passagedata' in the Twine archive file 'somefilename_readable' which has a non unique 'id' tag [someTwineId], in node 'someNodeName2'
     */
    public function testRunWithDuplicateTwineId()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode = new Textnode();

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $this->textnodeRepository->expects($this->once())
            ->method('save');

        $this->importTwine->run($importfile);
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLinkToAnotherTextnode()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[Linkdata->someNodeName2]]');
        $textnode1->setId('someId0');

        $textnode2 = new Textnode();
        $textnode2->setId('someId1');

        $this->hitchParserMock->expects(self::once())
            ->method('parseSingleArrowRight')
            ->with('Linkdata->someNodeName2')
            ->willReturn(
                [
                    'description' => 'some description',
                    'textnodeId' => 'someTextnodeId',
                    'status' => Textnode::HITCH_STATUS_ACTIVE,
                ]
            );

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p>', $textnode1->getText());
        self::assertEquals(1, $textnode1->getHitchCount());
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLinkToAnotherTextnodeDoubleArrowRight()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[description1-->textnodeId1]]');
        $textnode1->setId('someId0');

        $this->hitchParserMock->expects(self::once())
            ->method('parseDoubleArrowRight')
            ->with('description1-->textnodeId1')
            ->willReturn(
                [
                    'description' => 'description1',
                    'textnodeId' => 'textnodeId1',
                    'status' => Textnode::HITCH_STATUS_ACTIVE,
                ]
            );

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p>', $textnode1->getText());
        self::assertEquals(1, $textnode1->getHitchCount());
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLinkToAnotherTextnodeSingleArrowLeft()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[description1<-textnodeId1]]');
        $textnode1->setId('someId0');

        $this->hitchParserMock->expects(self::once())
            ->method('parseSingleArrowLeft')
            ->with('description1<-textnodeId1')
            ->willReturn(
                [
                    'description' => 'description1',
                    'textnodeId' => 'textnodeId1',
                    'status' => Textnode::HITCH_STATUS_ACTIVE,
                ]
            );

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p>', $textnode1->getText());
        self::assertEquals(1, $textnode1->getHitchCount());
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLinkToAnotherTextnodeSimpleHitch()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[description1]]');
        $textnode1->setId('someId0');

        $this->hitchParserMock->expects(self::once())
            ->method('parseSimpleHitch')
            ->with('description1')
            ->willReturn(
                [
                    'description' => 'description1',
                    'textnodeId' => 'textnodeId1',
                    'status' => Textnode::HITCH_STATUS_ACTIVE,
                ]
            );

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p>', $textnode1->getText());
        self::assertEquals(1, $textnode1->getHitchCount());
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLineBreakInText()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum'."\n"."foo bar");
        $textnode1->setId('someId0');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p><p>foo bar</p>', $textnode1->getText());
    }

    /**
     * tests a hitch to multiple other textnodes
     * @throws \Exception
     */
    public function testRunWithLinkToMultipleOtherTextnodes()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[Linkdata->someNodeName2]] [[Linkdata2->someNodeName3]]');
        $textnode1->setId('someId0');

        $textnode2 = new Textnode();
        $textnode2->setId('someId1');

        $textnode3 = new Textnode();
        $textnode3->setId('someId2');

        $this->hitchParserMock->expects(self::any())
            ->method('parseSingleArrowRight')
            ->willReturnCallback(function ($content, $name) {
                if ($content !== 'Linkdata->someNodeName2') {
                    return [
                        'description' => 'some description',
                        'textnodeId' => 'someTextnodeId1',
                        'status' => Textnode::HITCH_STATUS_ACTIVE,
                    ];
                }
                if ($content !== 'Linkdata2->someNodeName3') {
                    return [
                        'description' => 'some description',
                        'textnodeId' => 'someTextnodeId2',
                        'status' => Textnode::HITCH_STATUS_ACTIVE,
                    ];
                }
                self:: fail();
            });

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $returnValue = $this->importTwine->run($importfile);

        self::assertTrue($returnValue);
        self::assertEquals('<p>lorem ipsum</p>', $textnode1->getText());
        self::assertEquals(2, $textnode1->getHitchCount());
    }

    /**
     * tests run() with setting of metadata
     */
    public function testRunWithMetadataSetting()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum [[Linkdata->someNodeName2]]</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId2" position="104,30">lorem ipsum',
            'lorem ipsum [[metakey>:&lt;metavalue]]</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[metakey>:<metavalue]]');
        $textnode1->setId('someId0');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->will($this->returnValue($textnode1));

        $returnValue = $this->importTwine->run($importfile);
        self::assertTrue($returnValue);
        $metadata = $textnode1->getMetadata();
        self::assertInternalType('array', $metadata);
        self::assertArrayHasKey('metakey', $metadata);
        self::assertEquals('metavalue', $metadata['metakey']);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'tw-passagedata' in the Twine archive file 'somefilename_readable' which is missing its 'pid' attribute.
     */
    public function testRunWithMissingPid(): void
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $this->textnodeRepository->expects($this->never())
            ->method('find');

        $this->textnodeRepository->expects($this->never())
            ->method('findByTwineId');

        $this->importTwine->run($importfile);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'tw-passagedata' in the Twine archive file 'somefilename_readable' which hasn't a numeric value in its 'pid' attribute ('nonnumeric' was found instead).
     */
    public function testRunWithNonNumericPid(): void
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="nonnumeric" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $this->textnodeRepository->expects($this->never())
            ->method('find');

        $this->textnodeRepository->expects($this->never())
            ->method('findByTwineId');

        $this->importTwine->run($importfile);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage Nested 'tw-passagedata' found in Twine archive file 'somefilename_readable
     */
    public function testRunWithNestedPassageData(): void
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId2" position="104,40">Lorem Ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-passagedata>',
            '</tw-storydata>',
        ];

        $this->textnodeRepository->expects($this->never())
            ->method('find');

        $this->textnodeRepository->expects($this->once())
            ->method('findByTwineId');

        $this->importTwine->run($importfile);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /invalid element.*>:</
     */
    public function testRunWithMetadataSettingForInvalidFormat()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum [[Linkdata->someNodeName2]]</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId2" position="104,30">lorem ipsum',
            'lorem ipsum [[>:&lt;metavalue]]</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[>:<metavalue]]');
        $textnode1->setId('someId0');

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->will($this->returnValue($textnode1));

        $this->importTwine->run($importfile);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /contains the metadata field/
     */
    public function testRunWithMetadataSettingForAnAlreadyExistingMetadataKey()
    {
        $importfile = $this->getDummyImportfile();

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum [[Linkdata->someNodeName2]]</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId2" position="104,30">lorem ipsum',
            'lorem ipsum [[Autor>:&lt;metavalue]]</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setId('someId0');
        $textnode1->setMetadata(['metakey' => 'someValue']);

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->will($this->returnValue($textnode1));

        $this->importTwine->run($importfile);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Error .* occurred while parsing the Twine archive file 'somefilename_readable'/
     */
    public function testRunWithParserException(): void
    {
        self::$freadStack = [
            '<tw-storydata name="someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<tw-passagedata pid"1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setId('someId0');

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->willReturn($textnode1);

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->willReturn($textnode1);

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        $importfile = $this->getDummyImportfile();

        $this->importTwine->run($importfile);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'tw-storydata' in the Twine archive file 'somefilename_readable' which is missing its 'name' attribute
     */
    public function testRunWithInvalidStoryDataElement(): void
    {
        self::$freadStack = [
            '<tw-storydata startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        $textnode1 = new Textnode();
        $textnode1->setId('someId0');

        $this->textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->willReturn($textnode1);

        $this->textnodeRepository->expects($this->any())
            ->method('find')
            ->willReturn($textnode1);

        $this->fileExtractorMock->expects(self::any())
            ->method('extract')
            ->willReturn('readable.extracted');

        $importfile = $this->getDummyImportfile();

        $this->importTwine->run($importfile);
    }

    private function getDummyImportfile(array $data = []): Importfile
    {
        $default = [
            'filename'   => 'somefilename_readable',
            'licenseeId' => 'somelicenseeId',
            'author'     => 'someAuthor',
            'publisher'  => 'somePublisher',
            'id'         => 'someImportFileId',
        ];

        $importfileData = array_merge($default, $data);

        $importfile = new Importfile();
        if (null !== $importfileData['filename']) {
            $importfile->setFilename($importfileData['filename']);
        }
        if (null !== $importfileData['licenseeId']) {
            $importfile->setLicenseeId($importfileData['licenseeId']);
        }
        if (null !== $importfileData['author']) {
            $importfile->setAuthor($importfileData['author']);
        }
        if (null !== $importfileData['publisher']) {
            $importfile->setPublisher($importfileData['publisher']);
        }
        if (null !== $importfileData['id']) {
            $importfile->setId($importfileData['id']);
        }

        return $importfile;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TextNodeRepository
     */
    private function getTextnodeRepositoryMock()
    {
        return $this->getMockBuilder(TextNodeRepository::class)->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'field', 'equals', 'getQuery', 'save', 'find', 'findByTwineId', 'disableOrphanedNodes', 'setHyphenatedText'])->getMock();
    }

    private function getDmMock()
    {
        return $this->getMockBuilder('dmMock')->setMethods(['flush', 'persist', 'find'])->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HitchParser
     */
    private function createHitchParserMock(): HitchParser
    {
        return $this->createMock(HitchParser::class);
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
}
