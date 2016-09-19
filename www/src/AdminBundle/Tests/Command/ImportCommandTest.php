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

namespace AdminBundle\Command;

function is_readable($filename)
{
    return \strpos($filename, 'readable') !== false;
}

function file_exists($filename)
{
    return \strpos($filename, 'exists') !== false;
}

namespace AdminBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AdminBundle\Command\ImportCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ImportCommandTest
 */
class ImportCommandTest extends KernelTestCase
{
    private $mockObjects;
    private $command;
    private $commandTester;

    protected function setUp()
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
    public function testExecute()
    {
        $licenseeId = 'licenseeId';

        $this->mockObjects['importTwine']->expects($this->once())
            ->method('run')
            ->with('somefile_readable_exists.html', $licenseeId, 'someauthor', 'somepublisher');

        $returnValue = $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals('', $output);
        $this->assertEquals(0, $returnValue);
    }

    public function testExecuteWithUnreadableFile()
    {
        $this->mockObjects['importTwine']->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'twine-archive-file' => 'somefile_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('isn\'t readable', $output);
        $this->assertEquals(-1, $returnValue);
    }

    public function testExecuteWithFileNotExisting()
    {
        $this->mockObjects['importTwine']->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('doesn\'t exist', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     *
     */
    public function testExecuteWithExeptionInImportTwine()
    {
        $this->mockObjects['importTwine']->expects($this->once())
            ->method('run')
            ->with('somefile_readable_exists.html', 'licenseeId', 'someauthor', 'somepublisher')
            ->will($this->throwException(new \Exception('dummy Exception')));

        $this->mockObjects['importTwine']->expects($this->once())
            ->method('parserFree');

        $returnValue = $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'twine-archive-file' => 'somefile_readable_exists.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('dummy Exception', $output);
        $this->assertEquals(-1, $returnValue);
    }

    /**
     * @expectedException Exception
     */
    public function testExecuteWithLicenseeNotFound()
    {
        $this->mockObjects['importTwine']->expects($this->never())
            ->method('run');

        $returnValue = $this->commandTester->execute(array(
            'command'  => $this->command->getName(),
            'twine-archive-file' => 'somefile_exists_readable.html',
            '--licensee-name' => 'somelicensee2',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertContains('dummy Exception', $output);
        $this->assertEquals(-1, $returnValue);
        $this->expectException(\Exception::class);
    }

    private function getMockObjects()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $service = $this->getMockBuilder('Doctrine\Bundle\MongoDBBundle\ManagerRegistry')->disableOriginalConstructor()->getMock();
        $repositoryLicensee = $this->getMock('repositoryLicenseeMock', ['findOneByName']);
        $importTwine = $this->getMockBuilder('AdminBundle\Model\ImportTwine')->disableOriginalConstructor()->setMethods(['run', 'parserFree'])->getMock();
        $dm = $this->getMock('dmMock', ['flush']);

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
            'dm' => $dm
        ];
    }

    public function findOneByNameCallback($arg) {
        if ($arg === 'somelicensee') {
            $licenseeMock = $this->getMockBuilder('DembeloMain\Document\Licensee')->disableOriginalConstructor()->getMock();
            $licenseeMock->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('licenseeId'));
            return $licenseeMock;
        }
        return null;
    }
}
