<?php

/**
 * This file is part of the Dembelo.
 *
 * (c) Michael Giesler <michael@horsemen.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
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
