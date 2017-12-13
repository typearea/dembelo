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
 * @package AdminBundle
 */

// @codingStandardsIgnoreStart

namespace AdminBundle\Command;

/**
 * @param string $filename
 * @return bool
 */
function is_readable($filename)
{
    return \strpos($filename, 'readable') !== false;
}

/**
 * @param string $filename
 * @return bool
 */
function file_exists($filename)
{
    return \strpos($filename, 'exists') !== false;
}

namespace AdminBundle\Tests\Command;

use AdminBundle\Model\ImportTwine;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AdminBundle\Command\ImportCommand;
use Symfony\Component\Console\Tester\CommandTester;

// @codingStandardsIgnoreEnd

/**
 * Class ImportCommandTest
 */
class ImportCommandTest extends TestCase
{
    /**
     * @var ContainerAwareCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var ImportTwine|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importTwineMock;

    /**
     * @var LicenseeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $licenseeRepositoryMock;

    /**
     * @var TopicRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $topicRepositoryMock;

    /**
     * @var ImportfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importfileRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->importTwineMock = $this->createImportTwineMock();
        $this->licenseeRepositoryMock = $this->createLicenseeRepositoryMock();
        $this->topicRepositoryMock = $this->createTopicRepositoryMock();
        $this->importfileRepositoryMock = $this->createImportfileRepositoryMock();

        $this->command = new ImportCommand(
            $this->importTwineMock,
            $this->licenseeRepositoryMock,
            $this->topicRepositoryMock,
            $this->importfileRepositoryMock
        );

        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * tests the execute method
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->importTwineMock->expects($this->once())
            ->method('run')
            // @codingStandardsIgnoreStart
            ->will($this->returnCallback(function (Importfile $importfile): bool {
                return $importfile->getFilename() === 'somefile_readable_exists.html'
                    && $importfile->getLicenseeId() === 'licenseeId'
                    && $importfile->getAuthor() === 'someauthor'
                    && $importfile->getPublisher() === 'somepublisher'
                    && $importfile->getTopicId() === 'someTopic';
            }));
            // @codingStandardsIgnoreEnd

        $returnValue = $this->commandTester->execute([
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
            '--topic-name' => 'someTopic',
        ]);

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals('', $output);
        $this->assertEquals(0, $returnValue);
    }

    /**
     * tests execute method with unreadable file
     *
     * @return void
     */
    public function testExecuteWithUnreadableFile(): void
    {
        $this->importTwineMock->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute([
            'twine-archive-file' => 'somefile_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
            '--topic-name' => 'someTopic',
        ]);

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('isn\'t readable', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * tests execute() method with non existing file
     *
     * @return void
     */
    public function testExecuteWithFileNotExisting(): void
    {
        $this->importTwineMock->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute([
            'twine-archive-file' => 'somefile_readable.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
            '--topic-name' => 'someTopic',
        ]);

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('doesn\'t exist', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * tests execute() method with exception thrown by importTwine
     *
     * @return void
     */
    public function testExecuteWithExeptionInImportTwine(): void
    {
        $this->importTwineMock->expects($this->once())
            ->method('run')
            // @codingStandardsIgnoreStart
            ->will($this->returnCallback(function (Importfile $importfile): bool {
                return $importfile->getFilename() === 'somefile_readable_exists.html'
                    && $importfile->getLicenseeId() === 'licenseeId'
                    && $importfile->getAuthor() === 'someauthor'
                    && $importfile->getPublisher() === 'somepublisher'
                    && $importfile->getTopicId() === 'someTopic';
            }))
            // @codingStandardsIgnoreEnd
            ->will($this->throwException(new \Exception('dummy Exception')));

        $this->importTwineMock->expects($this->once())
            ->method('parserFree');

        $returnValue = $this->commandTester->execute(array(
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
            '--topic-name' => 'someTopic',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('dummy Exception', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteWithLicenseeNotFound(): void
    {
        $this->importTwineMock->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'twine-archive-file' => 'somefile_exists_readable.html',
            '--licensee-name' => 'somelicensee2',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('dummy Exception', $output);
        $this->assertEquals(-1, $returnValue);
        $this->expectException(\Exception::class);
    }

    /**
     * method for returnCallback()
     *
     * @param string $arg
     *
     * @return null|\PHPUnit_Framework_MockObject_MockObject|Licensee
     */
    public function findOneByNameCallback($arg): ?Licensee
    {
        if ('somelicensee' !== $arg) {
            return null;
        }

        $this->licenseeRepositoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('licenseeId'));

        return $this->licenseeRepositoryMock;
    }

    /**
     * method for returnCallback()
     *
     * @param string $arg
     *
     * @return null|\PHPUnit_Framework_MockObject_MockObject|Topic
     */
    public function findOneTopicByNameCallback($arg): ?Topic
    {
        if ('someTopic' !== $arg) {
            return null;
        }

        $topicMock = $this->getMockBuilder(Topic::class)->disableOriginalConstructor()->getMock();
        $topicMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('topicId'));

        return $topicMock;
    }

    /**
     * @return ImportTwine|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createImportTwineMock(): ImportTwine
    {
        return $this->createMock(ImportTwine::class);
    }

    /**
     * @return LicenseeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLicenseeRepositoryMock(): LicenseeRepositoryInterface
    {
        $mock = $this->createMock(LicenseeRepositoryInterface::class);

        $licenseeMock = $this->createMock(Licensee::class);
        $licenseeMock->method('getId')->willReturn('someLicenseeId');

        $mock->method('findOneBy')
            ->with(['name' => 'somelicensee'])
            ->willReturn($licenseeMock);

        return $mock;
    }

    /**
     * @return TopicRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTopicRepositoryMock(): TopicRepositoryInterface
    {
        $mock = $this->createMock(TopicRepositoryInterface::class);

        $topicMock = $this->createMock(Topic::class);
        $topicMock->method('getId')->willReturn('someTopicId');

        $mock->method('findOneBy')
            ->willReturn(['name' => 'someTopic'])
            ->willReturn($topicMock);

        return $mock;
    }

    /**
     * @return ImportfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createImportfileRepositoryMock(): ImportfileRepositoryInterface
    {
        return $this->createMock(ImportfileRepositoryInterface::class);
    }
}
