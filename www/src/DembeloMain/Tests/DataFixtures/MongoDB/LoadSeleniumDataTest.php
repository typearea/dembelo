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

namespace DembeloMain\Tests\DataFixtures\MongoDB;

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\DataFixtures\MongoDB\LoadSeleniumData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;

/**
 * Class DocumentTextnodeTest
 */
class LoadSeleniumDataTest extends WebTestCase
{
    /**
     * simple tests for main functionality
     */
    public function testLoad()
    {
        $phpunit = $this;
        $userRepositoryMock = $this->getUserRepositoryMock();
        $managerMock = $this->getManagerMock();
        $managerMock->expects($this->once())
            ->method('getRepository')
            ->with('DembeloMain:User')
            ->willReturn($userRepositoryMock);
        $managerMock->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($argument) use (&$phpunit) {
                $phpunit->assertInstanceOf(User::class, $argument);
            });
        $managerMock->expects($this->once())
            ->method('flush');

        $encoderMock = $this->getEncoderMock();
        $containerMock = $this->getContainerMock();
        $containerMock->expects($this->once())
            ->method('get')
            ->with('security.password_encoder')
            ->willReturn($encoderMock);

        $fixture = new LoadSeleniumData();
        $fixture->setContainer($containerMock);
        $fixture->load($managerMock);
    }

    private function getEncoderMock()
    {
        return $this->getMockBuilder(PasswordEncoder::class)
            ->setMethods(['encodePassword'])
            ->getMock();
    }

    private function getUserRepositoryMock()
    {
        return $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByEmail'])
            ->getMock();
    }

    private function getManagerMock()
    {
        return $this->getMockBuilder(ObjectManager::class)
            ->setMethods(['getRepository', 'persist', 'flush'])
            ->getMockForAbstractClass();
    }

    private function getContainerMock()
    {
        return $this->getMockBuilder(Container::class)
            ->setMethods(['get'])
            ->getMock();
    }
}
