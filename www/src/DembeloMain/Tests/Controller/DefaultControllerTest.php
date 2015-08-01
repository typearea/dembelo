<?php

/* Copyright (C) 2015 Michael Giesler
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

namespace DembeloMain\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * tests the index action
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Dembelo")')->count() > 0);
    }

    /**
     * tests the read action
     */
    public function testRead()
    {
        $client = static::createClient();

        $client->request('GET', '/themenfeld/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
