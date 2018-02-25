<?php
/* Copyright (C) 2018 Michael Giesler
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

namespace AdminBundle\IntegrationTests\Controller;

use DembeloMain\Document\Licensee;
use DembeloMain\Document\Textnode;
use DembeloMain\IntegrationTests\WebTestCase;

/**
 * @group integration
 */
class TextnodeControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testTextnodesActionForZeroTextnodes(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/textnodes');
        $response = $client->getResponse();
        self::assertNotNull($response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString('[]', $response->getContent());
    }

    /**
     * @return void
     */
    public function testTextnodesActionForMultipleTextnodes(): void
    {
        $licensee = new Licensee();
        $licensee->setName('some Licensee');
        $this->getMongo()->persist($licensee);
        $this->getMongo()->flush();

        $textnode1 = new Textnode();
        $textnode1->setCreated(new \DateTime());
        $textnode1->setStatus(Textnode::STATUS_ACTIVE);
        $textnode1->setLicenseeId($licensee->getId());
        $this->getMongo()->persist($textnode1);

        $textnode2 = new Textnode();
        $textnode2->setCreated(new \DateTime());
        $textnode2->setStatus(Textnode::STATUS_INACTIVE);
        $textnode2->setLicenseeId($licensee->getId());
        $this->getMongo()->persist($textnode2);

        $this->getMongo()->flush();

        $client = static::createClient();
        $client->request('POST', '/admin/textnodes');
        $response = $client->getResponse();
        self::assertNotNull($response);
        self::assertEquals(200, $response->getStatusCode(), $response->getContent());
        self::assertJson($response->getContent());
        $decoded = json_decode($response->getContent());
        self::assertInternalType('array', $decoded);
        self::assertCount(2, $decoded);
        self::assertInstanceOf(\stdClass::class, $decoded[0]);
        self::assertEquals('some Licensee', $decoded[0]->licensee);
    }
}
