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

namespace AdminBundle\Tests\Controller;

use AdminBundle\Controller\ImportfileController;
use AdminBundle\Model\ImportTwine;
use DembeloMain\Model\Repository\Doctrine\ODM\ImportfileRepository;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImportfileControllerTest extends WebTestCase
{
    /**
     * @var ImportfileController
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private $importfileRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportTwine
     */
    private $importTwineMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $mongoDbMock;

    /**
     * @var string
     */
    private $configTwineDirectory = '/tmp/phpunit-configTwineDirectory/';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->importfileRepositoryMock = $this->createImportfileRepositoryMock();
        $this->importTwineMock = $this->createMock(ImportTwine::class);
        $this->mongoDbMock = $this->createMock(ManagerRegistry::class);

        $this->controller = new ImportfileController(
            $this->importfileRepositoryMock,
            $this->importTwineMock,
            $this->mongoDbMock,
            $this->configTwineDirectory
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private function createImportfileRepositoryMock(): ImportfileRepositoryInterface
    {
        $repository = $this->getMockBuilder(ImportfileRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll'])
            ->getMock();

        return $repository;
    }
}