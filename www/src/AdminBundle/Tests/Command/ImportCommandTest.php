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

use DembeloMain\Document\Licensee;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AdminBundle\Command\ImportCommand;
use Symfony\Component\Console\Tester\CommandTester;

// @codingStandardsIgnoreEnd

/**
 * Class ImportCommandTest
 */
class ImportCommandTest extends KernelTestCase
{
    /**
     * @var array
     */
    private $mockObjects;

    /**
     * @var ContainerAwareCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new ImportCommand());
        $this->command = $application->find('dembelo:import');
        $this->commandTester = new CommandTester($this->command);
        $this->mockObjects = $this->getMockObjects();
        $this->command->setContainer($this->mockObjects['container']);
    }

    /**
     * tests the execute method
     */
    public function testExecute(): void
    {
        $this->mockObjects['importTwine']->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function ($importfile) {
                return $importfile->getFilename() === 'somefile_readable_exists.html'
                && $importfile->getLicenseeId() === 'licenseeId'
                && $importfile->getAuthor() === 'someauthor'
                && $importfile->getPublisher() === 'somepublisher';
            }));

        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals('', $output);
        $this->assertEquals(0, $returnValue);
    }

    /**
     * tests execute method with unreadable file
     */
    public function testExecuteWithUnreadableFile(): void
    {
        $this->mockObjects['importTwine']->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'twine-archive-file' => 'somefile_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('isn\'t readable', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * tests execute() method with non existing file
     */
    public function testExecuteWithFileNotExisting(): void
    {
        $this->mockObjects['importTwine']->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('doesn\'t exist', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * tests execute() method with exception thrown by importTwine
     */
    public function testExecuteWithExeptionInImportTwine(): void
    {
        $this->mockObjects['importTwine']->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function ($importfile) {
                return $importfile->getFilename() === 'somefile_readable_exists.html'
                && $importfile->getLicenseeId() === 'licenseeId'
                && $importfile->getAuthor() === 'someauthor'
                && $importfile->getPublisher() === 'somepublisher';
            }))
            ->will($this->throwException(new \Exception('dummy Exception')));

        $this->mockObjects['importTwine']->expects($this->once())
            ->method('parserFree');

        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher',
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
        $this->mockObjects['importTwine']->expects($this->never())
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
     * @return null|\PHPUnit_Framework_MockObject_MockObject|Licensee
     */
    public function findOneByNameCallback($arg): ?Licensee
    {
        if ($arg !== 'somelicensee') {
            return null;
        }

        $licenseeMock = $this->getMockBuilder(Licensee::class)->disableOriginalConstructor()->getMock();
        $licenseeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('licenseeId'));

        return $licenseeMock;
    }

    /**
     * @return array
     */
    private function getMockObjects(): array
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();

        $service = $this->getMockBuilder('Doctrine\Bundle\MongoDBBundle\ManagerRegistry')->disableOriginalConstructor()->getMock();
        $repositoryLicensee = $this->getMockBuilder('repositoryLicenseeMock')->setMethods(['findOneByName'])->getMock();
        $importTwine = $this->getMockBuilder('AdminBundle\Model\ImportTwine')->disableOriginalConstructor()->setMethods(['run', 'parserFree'])->getMock();
        $dm = $this->getMockBuilder('dmMock')->setMethods(['flush', 'persist'])->getMock();

        $container->expects($this->at(0))
            ->method("get")
            ->with($this->equalTo('admin.import.twine'))
            ->will($this->returnValue($importTwine));
        $container->expects($this->at(1))
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($service));
        $service->expects($this->any())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Licensee'))
            ->will($this->returnValue($repositoryLicensee));
        $service->expects($this->any())
            ->method("getManager")
            ->will($this->returnValue($dm));
        $repositoryLicensee->expects($this->any())
            ->method('findOneByName')
            ->will($this->returnCallback([$this, 'findOneByNameCallback']));

        return [
            'container' => $container,
            'service' => $service,
            'repositoryLicensee' => $repositoryLicensee,
            'importTwine' => $importTwine,
            'dm' => $dm,
        ];
    }
}
