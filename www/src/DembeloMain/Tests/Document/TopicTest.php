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
use DembeloMain\Document\Topic;

/**
 * Class DefaultControllerTest
 */
class TopicTest extends WebTestCase
{
    /**
     * @var DembeloMain\Document\Topic
     */
    private $topic;

    /**
     * setUp method
     */
    public function setUp()
    {
        $this->topic = new Topic();
    }

    /**
     * tests getId()
     */
    public function testGetIdShouldBeEqualSetId()
    {
        $this->topic->setId('testid');
        $this->assertEquals('testid', $this->topic->getId());
    }

    /**
     * tests getName()
     */
    public function testGetNameShouldBeEqualSetName()
    {
        $this->topic->setName('testname');
        $this->assertEquals('testname', $this->topic->getName());
    }

    /**
     * tests getStatus()
     */
    public function testGetStatusShouldBeEqualSetStatus()
    {
        $this->topic->setStatus('teststatus');
        $this->assertEquals('teststatus', $this->topic->getStatus());
    }
}
