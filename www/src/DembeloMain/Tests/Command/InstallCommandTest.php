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

use Apoutchika\LoremIpsumBundle\Services\LoremIpsum;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use DembeloMain\Command\InstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class DefaultControllerTest
 */
class InstallCommandTest extends TestCase
{
    /**
     * @var InstallCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var UserRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    /**
     * @var PasswordEncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordEncoderMock;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mongoMock;

    /**
     * @var TopicRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $topicRepositoryMock;

    /**
     * @var TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $textnodeRepositoryMock;

    /**
     * @var LicenseeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $licenseeRepositoryMock;

    /**
     * @var string
     */
    private $topicDummyImageDirectory = '/tmp/topicDummyImages/';

    /**
     * @var string
     */
    private $topicImageDirectory = '/tmp/topicImages/';

    /**
     * @var LoremIpsum|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loremIpsumMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->mongoMock = $this->createMongoMock();
        $this->topicRepositoryMock = $this->createTopicRepositoryMock();
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->licenseeRepositoryMock = $this->createLicenseeRepositoryMock();
        $this->userRepositoryMock = $this->createUserRepositoryMock();
        $this->loremIpsumMock = $this->createLoremIpsumMock();
        $this->passwordEncoderMock = $this->createPasswordEncoderMock();

        $this->command = new InstallCommand(
            $this->mongoMock,
            $this->topicRepositoryMock,
            $this->textnodeRepositoryMock,
            $this->licenseeRepositoryMock,
            $this->userRepositoryMock,
            $this->loremIpsumMock,
            $this->passwordEncoderMock,
            $this->topicDummyImageDirectory,
            $this->topicImageDirectory
        );
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * tests the install
     * @return void
     */
    public function testRunConfigure(): void
    {
        $returnValue = $this->commandTester->execute([]);

        // the output of the command in the console
        $output = $this->commandTester->getDisplay();
        $this->assertEquals('admin user installed'."\n".'Default users installed'."\n", $output);
        $this->assertEquals(0, $returnValue);
    }

    /**
     * @return UserRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserRepositoryMock(): UserRepositoryInterface
    {
        $methods = [
            'findOneByEmail',
            'find',
            'findByEmail',
            'findAll',
            'save',
            'findBy',
            'findOneBy',
            'getClassName',
        ];

        return $this->getMockBuilder(UserRepositoryInterface::class)->setMethods($methods)->getMock();
    }

    /**
     * @return BasePasswordEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPasswordEncoderMock(): BasePasswordEncoder
    {
        $methods = [
            'encodePassword',
            'isPasswordValid',
        ];
        $mock = $this->getMockBuilder(BasePasswordEncoder::class)->setMethods($methods)->getMock();

        $mock->expects($this->any())
            ->method('encodePassword')
            ->willReturn('encodedPassword');

        return $mock;
    }

    /**
     * @return ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMongoMock(): ManagerRegistry
    {
        return $this->createMock(ManagerRegistry::class);
    }

    /**
     * @return TopicRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTopicRepositoryMock(): TopicRepositoryInterface
    {
        return $this->createMock(TopicRepositoryInterface::class);
    }

    /**
     * @return TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTextnodeRepositoryMock(): TextNodeRepositoryInterface
    {
        return $this->createMock(TextNodeRepositoryInterface::class);
    }

    /**
     * @return LicenseeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLicenseeRepositoryMock(): LicenseeRepositoryInterface
    {
        return $this->createMock(LicenseeRepositoryInterface::class);
    }

    /**
     * @return LoremIpsum|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createLoremIpsumMock(): LoremIpsum
    {
        return $this->createMock(LoremIpsum::class);
    }
}
