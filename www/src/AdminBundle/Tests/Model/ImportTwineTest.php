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

namespace AdminBundle\Model;

use AdminBundle\Tests\Model\ImportTwineTest;

function fopen($filename)
{
    return strpos($filename, 'readable') !== false;
}

/**
 * used in extractTwineFile() and once in checkTwineFile and in initParser()
 * @return bool|mixed
 */
function fgets()
{
    if (empty(ImportTwineTest::$fgetsStack)) {
        return false;
    }
    return array_shift(ImportTwineTest::$fgetsStack);
}

function fputs($handle, $string)
{
    ImportTwineTest::$fputsStack[] = $string;
}

function fclose()
{

}

function fread()
{
    if (empty(ImportTwineTest::$freadStack)) {
        return false;
    }
    return array_shift(ImportTwineTest::$freadStack);
}

function feof()
{
    return empty(ImportTwineTest::$freadStack);
}

function fseek()
{
    return 0;
}

namespace AdminBundle\Tests\Model;

use AdminBundle\Model\ImportTwine;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class ImportTwineTest extends WebTestCase {

    public static $freadStack = [];
    public static $fgetsStack = [];
    public static $fputsStack = [];
    private $mocks;

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
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        $importTwine = new ImportTwine($mockObjects['container']);
        $importTwine->run($importfile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Failed to read data from file 'somefilename_readable'
     */
    public function testRunWithFreadReturnFalse()
    {
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        $importTwine = new ImportTwine($mockObjects['container']);
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
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = ['erste Zeile', 'zweite Zeile'];

        $importTwine = new ImportTwine($mockObjects['container']);
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
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [''];

        $importTwine = new ImportTwine($mockObjects['container']);
        $importTwine->run($importfile);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
            ->method('flush');
    }

    public function testRunWithCorrectButIncompleteData()
    {
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = ['<tw-storydata hurz>', 'zweite Zeile'];

        $importTwine = new ImportTwine($mockObjects['container']);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
            ->method('flush');
    }

    public function testRunButNoTextnodeIsWritten()
    {
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>' . "\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript">' . "\n",
            '</script><tw-passagedata pid="1" name="someNodeName" tags="Freigegeben" position="104,30">lorem impsum',
            'lorem impsum</tw-passagedata></tw-storydata>'
        ];
        self::$fgetsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];
        $expectedFputsStack = ['<tw-storydata > hurz', 'zweite Zeile', 'dritte Zeile</tw-storydata>'];

        $mockObjects['repositoryTopic']->expects($this->once())
            ->method('find')
            ->will($this->returnValue(true));

        $importTwine = new ImportTwine($mockObjects['container']);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);
        $mockObjects['dm']->expects($this->never())
            ->method('persist');
        $mockObjects['dm']->expects($this->never())
            ->method('flush');

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    public function testRunWithSingleNodeWithText()
    {
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>' . "\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript">' . "\n",
            '</script><tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>'
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
        $mockObjects['dm']->expects($this->never())
            ->method('flush');

        $importTwine = new ImportTwine($mockObjects['container']);
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
        $mockObjects = $this->getMockObjects();
        $importfile = new Importfile();
        $importfile->setFilename('somefilename_readable');
        $importfile->setLicenseeId('somelicenseeId');
        $importfile->setAuthor('someAuthor');
        $importfile->setPublisher('somePublisher');

        self::$freadStack = [
            '<tw-storydata ',
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>' . "\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript">' . "\n",
            '</script><tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>'
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
        $mockObjects['dm']->expects($this->never())
            ->method('flush');

        $mockObjects['textnode']->expects($this->once())
            ->method('getText')
            ->will($this->returnValue('Ein [[kapputter Link'));

        $importTwine = new ImportTwine($mockObjects['container']);
        $retVal = $importTwine->run($importfile);
        $this->assertTrue($retVal);

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    /**
     * @group current
     * @ssexpectedException Exception
     * @ssexpectedExceptionMessage The Twine archive file 'somefilename_readable' has a textnode named 'someNodeName1' which contains a malformed link that starts with '[[' but has no corresponding ']]'.
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
            '<tw-storydata name="someTopicId-->someStoryName" startnode="1" creator="Twine" creator-version="2.0.8" ifid="8E30D51C-4980-4161-B57F-B11C752E879A" format="Harlowe" options=""><style role="stylesheet" id="twine-user-stylesheet" type="text/twine-css"></style>' . "\n",
            '<script role="script" id="twine-user-script" type="text/twine-javascript">' . "\n",
            '</script><tw-passagedata pid="1" name="someNodeName1" tags="Freigegeben" position="104,30">lorem ipsum',
            'lorem ipsum</tw-passagedata></tw-storydata>'
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
        $mockObjects['dm']->expects($this->never())
            ->method('flush');

        $mockObjects['textnode']->expects($this->once())
            ->method('getText')
            ->will($this->returnValue('A [[working->hurz]] link'));

        $importTwine = new ImportTwine($mockObjects['container']);
        $retVal = $importTwine->run($twineArchivePath, $licenseeId, $author, $publisher);
        $this->assertTrue($retVal);

        $this->assertEquals($expectedFputsStack, self::$fputsStack);
    }

    private function getMockObjects()
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();

        $mongoMock = $this->getMockBuilder('Doctrine\Bundle\MongoDBBundle\ManagerRegistry')->disableOriginalConstructor()->setMethods(['getRepository', 'getManager'])->getMock();
        $repositoryLicensee = $this->getMock('repositoryLicenseeMock', ['findOneByName', 'find']);
        $importTwine = $this->getMockBuilder('AdminBundle\Model\ImportTwine')->disableOriginalConstructor()->setMethods(['run', 'parserFree'])->getMock();
        $dm = $this->getMock('dmMock', ['flush', 'persist', 'find']);
        $textnode = $this->getMock('DembeloMain\Document\Textnode');
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
            'container' => $container,
            'mongo' => $mongoMock,
            'repositoryLicensee' => $repositoryLicensee,
            'importTwine' => $importTwine,
            'dm' => $dm,
            'textnode' => $textnode,
            'repositoryTopic' => $this->mongoGetRepositoryCallback('DembeloMain:Topic')
        ];
    }

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

    public function mongoGetRepositoryCallback($arg)
    {
        if ($arg === 'DembeloMain:Topic') {
            if (!isset($this->mocks['DembeloMain:Topic'])) {
                $this->mocks['DembeloMain:Topic'] = $this->getMock('repositoryTopicMock', ['findOneByName', 'find']);
            }

            return $this->mocks['DembeloMain:Topic'];
        } else if ($arg === 'DembeloMain:Textnode') {
            if (!isset($this->mocks['DembeloMain:Textnode'])) {
                $this->mocks['DembeloMain:Textnode'] = $this->getMock('repositoryTextnode', ['createQueryBuilder', 'field', 'equals', 'getQuery']);
            }
            return $this->mocks['DembeloMain:Textnode'];
        }
    }

}
