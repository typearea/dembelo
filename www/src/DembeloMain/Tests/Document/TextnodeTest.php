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

namespace DembeloMain\Tests\Document;

use DembeloMain\Document\Licensee;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Document\Textnode;
use DembeloMain\Document\Topic;

/**
 * Class DocumentTextnodeTest
 */
class TextnodeTest extends WebTestCase
{
    /**
     * @var DembeloMain\Document\Textnode
     */
    private $textnode;

    /**
     * setUp method
     */
    public function setUp()
    {
        $this->textnode = new Textnode();
    }

    /**
     * tests getId()
     */
    public function testGetIdShouldBeEqualSetId()
    {
        $this->textnode->setId('testid');
        $this->assertEquals('testid', $this->textnode->getId());
    }

    /**
     * tests getId()
     */
    public function testGetIdShouldBeNullWhenNotSet()
    {
        $this->assertNull($this->textnode->getId());
    }

    /**
     * tests the created
     */
    public function testCreated()
    {
        $this->textnode->setCreated('2015-01-01 01:02:03');
        $this->assertEquals('2015-01-01 01:02:03', $this->textnode->getCreated());
    }

    /**
     * tests the licensee ID
     */
    public function testLicenseeId()
    {
        $licenseeId = 'asd23fasdf';
        $this->textnode->setLicenseeId($licenseeId);
        $this->assertEquals($licenseeId, $this->textnode->getLicenseeId());
    }

    /**
     * tests the metadata
     */
    public function testMetadata()
    {
        $metadata = array('story' => 'xyz');
        $this->textnode->setMetadata($metadata);
        $this->assertEquals($metadata, $this->textnode->getMetadata());
    }

    /**
     * tests the status
     */
    public function testStatus()
    {
        $status = 1;
        $this->textnode->setStatus($status);
        $this->assertEquals($status, $this->textnode->getStatus());
    }

    /**
     * tests the text
     */
    public function testText()
    {
        $text = 'Lorem Ipsum';
        $this->textnode->setText($text);
        $this->assertEquals($text, $this->textnode->getText());
    }

    /**
     * tests the topicId
     */
    public function testTopicId()
    {
        $topicId = 'asd23123';
        $this->textnode->setTopicId($topicId);
        $this->assertEquals($topicId, $this->textnode->getTopicId());
    }

    /**
     * tests the access
     */
    public function testAccess()
    {
        $this->textnode->setAccess(false);
        $this->assertFalse($this->textnode->getAccess());
    }
}
