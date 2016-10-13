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
    private $mocks;

    /**
     * resets some variables
     */
    public function setUp()
    {
        self::$freadStack = self::$fgetsStack = self::$fputsStack = [];
        $this->mocks = [];
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to read data from file 'somefilename'
     */
    public function testRunWithFopenIsFalse()
    {
        $textnodeRepository = $this->getTextnodeRepositoryMock();
        $topicRepository = $this->getTopicRepositoryMock();

        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

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

        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
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

        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = ['erste Zeile', 'zweite Zeile'];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
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

        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [''];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $importTwine->run($importfile);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
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

        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = ['<tw-storydata hurz>', 'zweite Zeile'];

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
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

        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName" tags="Freigegeben" position="104,30">lorem impsum',
            'lorem impsum</tw-passagedata></tw-storydata>',
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

        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
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

        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
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

        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

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

        $importfile = new Importfile();
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        $importTwine = new ImportTwine($textnodeRepository, $topicRepository);
        $retVal = $importTwine->run($importfile);
    }

    /**
     * still doesn't work
     *
     * @expectedException Exception
     * @expectedExceptionMessage The Twine archive file 'somefilename_readable' has a textnode named 'someNodeName1' which contains a malformed link that starts with '[[' but has no corresponding ']]'.
     */
    public function xtestRun()
    {
        $mockObjects = $this->getMockObjects();
        $twineArchivePath = 'somefilename_readable';
        $licenseeId = 'somelicenseeId';
        $author = 'someAuthor';
        $publisher = 'somePublisher';
        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>'."\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript"></script>'."\n",
            '<tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>',
        ];
        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];
        $expectedFputsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $mockObjects['repositoryTopic']->expects($this->once())
            ->method('find')
            ->will($this->returnValue(true));

        $mockObjects['dm']->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($textnode) {
                return $textnode instanceof Textnode
                && $textnode->getText() === "lorem ipsumlorem ipsum";
            }));

        $mockObjects['textnode']->expects($this->once())
            ->method('getText')
            ->will($this->returnValue('A [[working->hurz]] link'));

        $importTwine = new ImportTwine();
        $retVal = $importTwine->run($twineArchivePath, $licenseeId, $author, $publisher);
        $this->assertTrue($retVal);

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    /**
     * callback method for a returnCallback()
     *
     * @param string $arg
     * @return null|\PHPUnit_Framework_MockObject_MockObject
     */
    public function findOneByNameCallback($arg)
    {
        if ($arg === 'somelicensee') {
            $licenseeMock = $this->getMockBuilder('DembeloMain\Document\Licensee')->disableOriginalConstructor()->getMock();
            $licenseeMock->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('licenseeId'));

            return $licenseeMock;
        }

        return null;
    }

    /**
     * callback method for a returnCallback()
     *
     * @param string $arg
     * @return mixed
     */
    public function mongoGetRepositoryCallback($arg)
    {
        if ($arg === 'DembeloMain:Topic') {
            if (!isset($this->mocks['DembeloMain:Topic'])) {
                $this->mocks['DembeloMain:Topic'] = $this->getMock('repositoryTopicMock', ['findOneByName', 'find']);
            }

            return $this->mocks['DembeloMain:Topic'];
        }

        return false;
    }

    private function getTextnodeRepositoryMock()
    {
        return $this->getMockBuilder(TextNodeRepository::class)->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'field', 'equals', 'getQuery', 'save', 'find'])->getMock();
    }

    private function getTopicRepositoryMock()
    {
        return $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['find'])->getMock();
    }

    /**
     * builds all mock objects
     *
     * @return array
     */
    private function getMockObjects()
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();

        $mongoMock = $this->getMockBuilder('Doctrine\Bundle\MongoDBBundle\ManagerRegistry')->disableOriginalConstructor()->setMethods(['getRepository', 'getManager'])->getMock();
        $repositoryLicensee = $this->getMockBuilder('repositoryLicenseeMock')->setMethods(['findOneByName', 'find'])->getMock();
        $importTwine = $this->getMockBuilder('AdminBundle\Model\ImportTwine')->disableOriginalConstructor()->setMethods(['run', 'parserFree'])->getMock();
        $dm = $this->getMockBuilder('dmMock')->setMethods(['flush', 'persist', 'find'])->getMock();
        $textnode = $this->getMockBuilder('DembeloMain\Document\Textnode')->getMock();
        $container->expects($this->any())
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($mongoMock));
        $mongoMock->expects($this->any())
            ->method("getRepository")
            ->will($this->returnCallback([$this, 'mongoGetRepositoryCallback']));
        $mongoMock->expects($this->any())
            ->method("getManager")
            ->will($this->returnValue($dm));
        $repositoryLicensee->expects($this->any())
            ->method('findOneByName')
            ->will($this->returnCallback([$this, 'findOneByNameCallback']));
        $dm->expects($this->any())
            ->method('find')
            ->with($this->equalTo('DembeloMain:Textnode'), $this->equalTo(null))
            ->will($this->returnValue($textnode));

        return [
            'mongo' => $mongoMock,
            'repositoryLicensee' => $repositoryLicensee,
            'importTwine' => $importTwine,
            'dm' => $dm,
            'textnode' => $textnode,
        ];
    }
}
