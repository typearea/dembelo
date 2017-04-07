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


/**
 * @package DembeloMain
 */

namespace DembeloMain\Tests\Command;

use DembeloMain\Model\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DembeloMain\Command\InstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

/**
 * Class DefaultControllerTest
 */
class InstallCommandTest extends KernelTestCase
{
    private $containerMock;
    /* @var InstallCommand */
    private $command;
    /* @var CommandTester */
    private $commandTester;

    private $userRepositoryMock;
    private $passwordEncoderMock;

    protected function setUp()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new InstallCommand());
        $this->command = $application->find('dembelo:install');
        $this->commandTester = new CommandTester($this->command);
        $this->containerMock = $this->getContainerMock();
        $this->command->setContainer($this->containerMock);
    }

    /**
     * tests the install
     */
    public function testRunConfigure()
    {
        $returnValue = $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            //'twine-archive-file' => 'somefile_readable_exists.html',
            //'--licensee-name' => 'somelicensee',
            //'--metadata-author' => 'someauthor',
            //'--metadata-publisher' => 'somepublisher',
        ));

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals('admin user installed'."\n".'Default users installed'."\n", $output);
        $this->assertEquals(0, $returnValue);
    }

    private function getUserRepositoryMock()
    {
        $methods = [
            'findOneByEmail',
            'find',
            'findByEmail',
            'findAll',
            'save',
        ];
        $this->userRepositoryMock = $this->getMockBuilder(UserRepositoryInterface::class)->setMethods($methods)->getMock();
    }

    private function getPasswordEncoderMock()
    {
        $methods = [
            'encodePassword',
            'isPasswordValid',
        ];
        $this->passwordEncoderMock = $this->getMockBuilder(BasePasswordEncoder::class)->setMethods($methods)->getMock();

        $this->passwordEncoderMock->expects($this->any())
            ->method('encodePassword')
            ->willReturn('encodedPassword');
    }

    private function getContainerMock()
    {
        $this->getUserRepositoryMock();
        $this->getPasswordEncoderMock();
        $containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $containerMock->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($param) {
                switch ($param) {
                    case 'app.model_repository_user':
                        return $this->userRepositoryMock;
                    case 'security.password_encoder':
                        return $this->passwordEncoderMock;
                }
            });

        return $containerMock;
    }
}
