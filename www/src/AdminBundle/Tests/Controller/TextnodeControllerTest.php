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

use AdminBundle\Controller\TextnodeController;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TextnodeControllerTest
 */
class TextnodeControllerTest extends TestCase
{
    /**
     * @var TextnodeController
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepositoryInterface
     */
    private $textnodeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private $importfileRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LicenseeRepositoryInterface
     */
    private $licenseeRepositoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->importfileRepositoryMock = $this->createImportfileRepositoryMock();
        $this->licenseeRepositoryMock = $this->createLicenseeRepositoryMock();

        $this->controller = new TextnodeController(
            $this->textnodeRepositoryMock,
            $this->importfileRepositoryMock,
            $this->licenseeRepositoryMock
        );
    }

    /**
     * tests textnode action
     * @return void
     */
    public function testTextnodesAction(): void
    {
        $textnode = new Textnode();
        $textnode->setId('someId');
        $textnode->setCreated(new \DateTime('2017-01-01 12:00:00'));
        $textnode->setStatus(1);
        $textnode->setAccess(true);
        $textnode->setLicenseeId('someLicenseeId');
        $textnode->setArbitraryId('someArbitraryId');
        $textnode->setTwineId('someTwineId');
        $textnode->setMetadata(['key1' => 'val1', 'key2' => 'val2']);

        $licensee = new Licensee();
        $licensee->setId('someLicenseeId');
        $licensee->setName('someLicenseeName');

        $importfile = new Importfile();
        $importfile->setId('someImportfileId');
        $importfile->setName('someImportfileName');

        $this->textnodeRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$textnode]);

        $this->licenseeRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$licensee]);

        $this->importfileRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$importfile]);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->textnodesAction();
        $this->assertInstanceOf(Response::class, $response);
        $expectedJson = '[{"id":"someId","status":"aktiv","created":"01.01.2017, 12:00:00",';
        $expectedJson .= '"access":"ja","licensee":"someLicenseeName","importfile":"unbekannt","beginning":"...",';
        $expectedJson .= '"financenode":"ja","arbitraryId":"someArbitraryId","twineId":"someTwineId",';
        $expectedJson .= '"metadata":"key1: val1\nkey2: val2\n",';
        $expectedJson .= '"parentnodes":"","childnodes":""}]';
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    /**
     * @return TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTextnodeRepositoryMock(): TextNodeRepositoryInterface
    {
        return $this->createMock(TextNodeRepositoryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private function createImportfileRepositoryMock(): ImportfileRepositoryInterface
    {
        $repository = $this->getMockBuilder(ImportfileRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll', 'save', 'find', 'findBy', 'findOneBy', 'getClassName'])
            ->getMock();

        return $repository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LicenseeRepositoryInterface
     */
    private function createLicenseeRepositoryMock(): LicenseeRepositoryInterface
    {
        $repository = $this->getMockBuilder(LicenseeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll', 'save', 'find', 'findBy', 'findOneBy', 'getClassName', 'findOneByName'])
            ->getMock();

        return $repository;
    }
}
