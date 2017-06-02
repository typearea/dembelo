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
 *
 * @return bool|mixed
 */
function fgets()
{
    if (empty(ImportTwineTest::$fgetsStack)) {
        return false;
    }

    return array_shift(ImportTwineTest::$fgetsStack);
}

/**
 * mock function
 *
 * @param Resource $handle
 * @param string   $string
 */
function fputs($handle, $string)
{
    ImportTwineTest::$fputsStack[] = $string;
}

/**
 * mock function
 */
function fclose()
{
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
 * mock function
 *
 * @return int
 */
function fseek()
{
    return 0;
}

/**
 * @param Resource $parser
 */
function xml_parser_free($parser)
{
    ImportTwineTest::$parserFreeCalled = true;

    return \xml_parser_free($parser);
}

namespace AdminBundle\Tests\Model;

use AdminBundle\Model\ImportTwine;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

// @codingStandardsIgnoreEnd

/**
 * Class ImportTwineTest
 * @package AdminBundle\Tests\Model
 */
class ImportTwineTest extends WebTestCase
{

    public static $freadStack = [];
    public static $fgetsStack = [];
    public static $fputsStack = [];
    public static $parserFreeCalled = false;
    private $mocks;

    /**
     * resets some variables
     */
    public function setUp()
    {
        self::$freadStack = self::$fgetsStack = self::$fputsStack = [];
        $this->mocks = [];
        self::$parserFreeCalled = false;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to read data from file 'somefilename'
     */
    public function testRunWithFopenIsFalse()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile(['filename' => 'somefilename']);

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to read data from file 'somefilename_readable'
     */
    public function testRunWithFreadReturnFalse()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();
        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage File 'somefilename_readable' isn't a Twine archive file
     */
    public function testRunWithWrongFileFormat()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        self::$freadStack = ['erste Zeile', 'zweite Zeile'];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
        $dm->expects($this->never())
            ->method('persist');
        $dm->expects($this->never())
            ->method('flush');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage File 'somefilename_readable' seems to be empty.
     */
    public function testRunWithEmptyFirstLine()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        self::$freadStack = [''];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
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
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $dm = $this->getDmMock();
        $importfile = $this->getDummyImportfile();

        self::$freadStack = ['<tw-storydata hurz>', 'zweite Zeile'];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
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
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName" tags="Freigegeben ID:foobar" position="104,30">lorem impsum',
            'lorem impsum</tw-passagedata></tw-storydata>',
        ];
        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];
        $expectedFputsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $textnodeRepository->expects($this->any())
            ->method('findByTwineId')
            ->will($this->returnValue(null));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);
        $textnodeRepository->expects($this->never())
            ->method('save');

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    /**
     * tests the run method with a single node containing text
     *
     * @throws \Exception
     */
    public function testRunWithSingleNodeWithText()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];
        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];
        $expectedFputsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($textnode) {
                return $textnode instanceof Textnode
                    && $textnode->getText() === "lorem ipsumlorem ipsum";
            }));

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The Twine archive file 'somefilename_readable' has a textnode named 'someNodeName1' which contains a malformed link that starts with '[[' but has no corresponding ']]'.
     */
    public function testRunWithUnfinishedLinkTag()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];
        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];
        $expectedFputsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();
        $textnode->setText('ein [[kaputter Link');

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $textnodeRepository->expects($this->any())
            ->method('save')
            ->with($this->callback(function ($textnode) {
                return $textnode instanceof Textnode
                && $textnode->getText() === "lorem ipsumlorem ipsum";
            }));

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    /**
     * check if exception is thrown when no licensee is available
     *
     * @expectedException Exception
     * @expectedExceptionMessage no licensee available
     */
    public function testRunWithExceptionWhenNoLicenseeIsAvailable()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile(['licenseeId' => null]);

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
    }

    /**
     * check if exception is thrown when no licensee is available
     *
     * @expectedException Exception
     * @expectedExceptionMessage no filename available
     */
    public function testRunWithExceptionWhenNoFilenameIsAvailable()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile(['filename' => null]);

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage no ID given for Textnode "someNodeName1"
     */
    public function testRunWithTextnodeWithoutTwineID()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->never())
            ->method('save');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage no ID given for Textnode "someNodeName1"
     */
    public function testRunWithTextnodeWithoutAnyTag()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->never())
            ->method('save');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * tests identifying a textnode by a twine ID
     * @throws \Exception
     */
    public function testRunWithTextnodeWithTwineID()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * tests the freeing of xml parser
     * @throws \Exception
     */
    public function testParserFree()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);

        $this->assertTrue(self::$parserFreeCalled);
    }

    /**
     * tests disabling of a textnode that is no longer found in import file
     * @throws \Exception
     */
    public function testRunWithTextnodeDeletedInImportfile()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben ID:foobar" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();
        $textnode->setId('someTextnodeId');

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->once())
            ->method('findByTwineId')
            ->with($importfile, 'foobar')
            ->will($this->returnValue($textnode));

        $textnodeRepository->expects($this->once())
            ->method('disableOrphanedNodes')
            ->with($importfile, [$textnode->getId()]);

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage There is a 'tw-passagedata' in the Twine archive file 'somefilename_readable' which has a non unique 'id' tag [someTwineId], in node 'someNodeName2'
     */
    public function testRunWithDuplicateTwineId()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode = new Textnode();

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->once())
            ->method('save');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage There is a textnode which references another textnode named 'someNodeName3', but this textnode doesn't exist within the same story.
     */
    public function testRunWithLinkToNonExistingTextnode()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum [[Linkdata->someNodeName3]]</tw-passagedata>',
            '</tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[Linkdata->someNodeName3]]');

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->any())
            ->method('save');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
    }

    /**
     * tests a hitch to another textnode
     * @throws \Exception
     */
    public function testRunWithLinkToAnotherTextnode()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $importfile = $this->getDummyImportfile();

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="ID:someTwineId1" position="104,30">lorem ipsum',
            'lorem ipsum [[Linkdata->someNodeName2]]</tw-passagedata>',
            '<tw-passagedata pid="2" name="someNodeName2" tags="ID:someTwineId2" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata>',
            '</tw-storydata>',
        ];

        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $textnode1 = new Textnode();
        $textnode1->setText('lorem ipsum [[Linkdata->someNodeName2]]');
        $textnode1->setId('someId0');

        $textnode2 = new Textnode();
        $textnode2->setId('someId1');

        $textnodeRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($textnode1));

        $topicRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue(true));

        $textnodeRepository->expects($this->any())
            ->method('save')
            ->willReturnCallback(function ($textnode) {
                static $counter = 0;
                $textnode->setId('someTextnode'.$counter++);
            });

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $returnValue = $importTwine->run($importfile);

        $this->assertTrue($returnValue);

    }

    private function getDummyImportfile(array $data = [])
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
        if (!is_null($importfileData['filename'])) {
            $importfile->setFilename($importfileData['filename']);
        }
        if (!is_null($importfileData['licenseeId'])) {
            $importfile->setLicenseeId($importfileData['licenseeId']);
        }
        if (!is_null($importfileData['author'])) {
            $importfile->setAuthor($importfileData['author']);
        }
        if (!is_null($importfileData['publisher'])) {
            $importfile->setPublisher($importfileData['publisher']);
        }
        if (!is_null($importfileData['id'])) {
            $importfile->setId($importfileData['id']);
        }

        return $importfile;
    }

    private function getTextnodeRepositoryMock()
    {
        return $this->getMockBuilder(TextNodeRepository::class)->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'field', 'equals', 'getQuery', 'save', 'find', 'findByTwineId', 'disableOrphanedNodes', 'setHyphenatedText'])->getMock();
    }

    private function getTopicRepositoryMock()
    {
        return $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['find'])->getMock();
    }

    private function getDmMock()
    {
        return $this->getMockBuilder('dmMock')->setMethods(['flush', 'persist', 'find'])->getMock();
    }
}
