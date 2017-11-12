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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LicenseeControllerTest extends WebTestCase
{
    /**
     * tests licenseeAction with no licensees
     * @return void
     */
    public function testLicenseeActionWithNoLicensees(): void
    {
        $this->licenseeRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->licenseesAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests licenseeAction() with one licensee
     * @return void
     */
    public function testLicenseeActionWithOneLicensee(): void
    {
        $licensee = new Licensee();
        $licensee->setName('someName');
        $licensee->setId('someId');

        $this->licenseeRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$licensee]);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->licenseesAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }
}