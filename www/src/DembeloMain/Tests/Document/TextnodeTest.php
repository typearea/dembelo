<?php

/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
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
     * @var \DembeloMain\Document\Textnode
     */
    private $textnode;

    /**
     * setUp method
     * @return void
     */
    public function setUp(): void
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

    /**
     * tests the access
     */
    public function testTwineId()
    {
        $this->textnode->setTwineId('foobarTwineId');
        $this->assertEquals('foobarTwineId', $this->textnode->getTwineId());
    }

    /**
     * Tests that no hitches are present after the Textnode was constructed.
     */
    public function testGetHitchCountAfterTextnodeConstruction()
    {
        $this->assertEquals($this->textnode->getHitchCount(), 0);
    }

    /**
     * Tests if the hitches gets correctly counted after their insertion.
     */
    public function testGetHitchCount()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4578";
        $hitch['description'] = "Continue 2.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 2);
    }

    /**
     * Tests if a hitch can be correctly retrieved after its insertion.
     */
    public function testGetHitch()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $result = $this->textnode->getHitch($this->textnode->getHitchCount() - 1);

        $this->assertFalse(is_null($result));

        $this->assertEquals($result['textnodeId'], "55f5ab3708985c4b188b4577");
        $this->assertEquals($result['description'], "Continue.");
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_ACTIVE);
    }

    /**
     * Tests if retrieving a hitch fails if a negative index is passed.
     */
    public function testGetHitchNegativeIndex()
    {
        $result = $this->textnode->getHitch(-1);

        $this->assertTrue(is_null($result));
    }

    /**
     * Tests if retrieving a hitch fails if a too high index is passed.
     */
    public function testGetHitchTooHighIndex()
    {
        $result = $this->textnode->getHitch(0);

        $this->assertTrue(is_null($result));
    }

    /**
     * Tests if the success of adding a hitch gets correctly signalled.
     */
    public function testAppendHitch()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
    }

    /**
     * Tests if the attempt of adding a hitch fails if the textnodeId is missing.
     */
    public function testAppendHitchWithoutTextnodeId()
    {
        $hitch = [];
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertFalse($this->textnode->appendHitch($hitch));
    }

    /**
     * Tests if the attempt of adding a hitch fails if the description is missing.
     */
    public function testAppendHitchWithoutDescription()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertFalse($this->textnode->appendHitch($hitch));
    }

    /**
     * Tests if the attempt of adding a hitch fails if the status is missing.
     */
    public function testAppendHitchWithoutStatus()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";

        $this->assertFalse($this->textnode->appendHitch($hitch));
    }

    /**
     * Tests if the attempt of adding a hitch fails if the textnodeId is empty.
     */
    public function testAppendHitchWithEmptyTextnodeId()
    {
        $hitch = [];
        $hitch['textnodeId'] = null;
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertFalse($this->textnode->appendHitch($hitch));
    }

    /**
     * Tests if appending more hitches than the maximum amount of
     * hitches is rejected.
     *
     * @return void
     */
    public function testAppendHitchMoreThanMaximum(): void
    {
        for ($i = 0; $i < Textnode::HITCHES_MAXIMUM_COUNT + 1; ++$i) {
            $hitch = [];
            $hitch['textnodeId'] = $i;
            $hitch['description'] = 'Continue.';
            $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

            if ($i < Textnode::HITCHES_MAXIMUM_COUNT) {
                $this->assertTrue($this->textnode->appendHitch($hitch), 'adding hitch no. '.$i);
            } else {
                $this->assertFalse($this->textnode->appendHitch($hitch), 'adding hitch no. '.$i);
            }
        }
    }

    /**
     * Tests if an old hitch can be replaced by a new hitch.
     */
    public function testSetHitch()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4578";
        $hitch['description'] = "Abort.";
        $hitch['status'] = Textnode::HITCH_STATUS_INACTIVE;
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitch);

        $this->assertTrue($result);
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $result = $this->textnode->getHitch($this->textnode->getHitchCount() - 1);

        $this->assertFalse(is_null($result));

        $this->assertEquals($result['textnodeId'], "55f5ab3708985c4b188b4578");
        $this->assertEquals($result['description'], "Abort.");
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_INACTIVE);
    }

    /**
     * Tests if updating a hitch fails if a negative index is passed.
     */
    public function testSetHitchNegativeIndex()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertFalse($this->textnode->setHitch(-1, $hitch));
    }

    /**
     * Tests if updating a hitch fails if a too high index is passed.
     */
    public function testSetHitchTooHighIndex()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertFalse($this->textnode->setHitch(1, $hitch));
    }

    /**
     * Tests if the attempt of updating a hitch fails if the textnodeId is missing.
     */
    public function testSetHitchWithoutTextnodeId()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['description'] = "Abort.";
        $hitch['status'] = Textnode::HITCH_STATUS_INACTIVE;
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitch);

        $this->assertFalse($result);
    }

    /**
     * Tests if the attempt of updating a hitch fails if the description is missing.
     */
    public function testSetHitchWithoutDescription()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4578";
        $hitch['status'] = Textnode::HITCH_STATUS_INACTIVE;
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitch);

        $this->assertFalse($result);
    }

    /**
     * Tests if the attempt of updating a hitch fails if the status is missing.
     */
    public function testSetHitchWithoutStatus()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4578";
        $hitch['description'] = "Abort.";
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitch);

        $this->assertFalse($result);
    }

    /**
     * Tests if the attempt of updating a hitch fails if the textnodeId is empty.
     */
    public function testSetHitchWithEmptyTextnodeId()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->textnode->appendHitch($hitch);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = null;
        $hitch['description'] = "Abort.";
        $hitch['status'] = Textnode::HITCH_STATUS_INACTIVE;
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitch);

        $this->assertFalse($result);
    }

    /**
     * Tests if the success of removing a hitch gets correctly signalled.
     */
    public function testRemoveHitch()
    {
        $hitch = [];
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4577";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $this->assertTrue($this->textnode->removeHitch($this->textnode->getHitchCount() - 1));
        $this->assertEquals($this->textnode->getHitchCount(), 0);
    }

    /**
     * Tests if removing a hitch fails if there's no hitch present.
     */
    public function testRemoveHitchThatsNotPresent()
    {
        $this->assertFalse($this->textnode->removeHitch(1));
    }

    /**
     * Tests if removing a hitch fails if a negative index is passed.
     */
    public function testRemoveHitchNegativeIndex()
    {
        $hitch = [];
        $hitch['textnodeId'] = '55f5ab3708985c4b188b4577';
        $hitch['description'] = 'Continue.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $this->assertFalse($this->textnode->removeHitch(-1));
    }

    /**
     * Tests if removing a hitch fails if a too high index is passed.
     * @return void
     */
    public function testRemoveHitchTooHighIndex(): void
    {
        $hitch = [];
        $hitch['textnodeId'] = '55f5ab3708985c4b188b4577';
        $hitch['description'] = 'Continue.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $this->assertFalse($this->textnode->removeHitch(2));
    }

    /**
     * Tests if the correct hitch gets removed.
     * @return void
     */
    public function testRemoveHitchSpecific(): void
    {
        $hitch = [];
        $hitch['textnodeId'] = '55f5ab3708985c4b188b4577';
        $hitch['description'] = 'Continue.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitch = [];
        $hitch['textnodeId'] = '55f5ab3708985c4b188b4578';
        $hitch['description'] = 'More.';
        $hitch['status'] = Textnode::HITCH_STATUS_INACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 2);

        $hitch = [];
        $hitch['textnodeId'] = '55f5ab3708985c4b188b4579';
        $hitch['description'] = 'Abort.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->assertTrue($this->textnode->appendHitch($hitch));
        $this->assertEquals($this->textnode->getHitchCount(), 3);

        $this->assertTrue($this->textnode->removeHitch(1));

        $result = $this->textnode->getHitch(0);
        $this->assertNotNull($result);

        $this->assertEquals($result['textnodeId'], '55f5ab3708985c4b188b4577');
        $this->assertEquals($result['description'], 'Continue.');
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_ACTIVE);

        $result = $this->textnode->getHitch(1);
        $this->assertNotNull($result);

        $this->assertEquals($result['textnodeId'], "55f5ab3708985c4b188b4579");
        $this->assertEquals($result['description'], "Abort.");
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_ACTIVE);

        $result = $this->textnode->getHitch(2);
        $this->assertNull($result);
    }

    /**
     * tests the access
     */
    public function testGetArbitraryId()
    {
        $this->textnode->setArbitraryId('foobarArbitraryId');
        $this->assertEquals('foobarArbitraryId', $this->textnode->getArbitraryId());
    }

    /**
     * tests the text
     * @return void
     */
    public function testTextHyphenated(): void
    {
        $text = 'Lorem Ipsum';
        $this->textnode->setTextHyphenated($text);
        $this->assertEquals($text, $this->textnode->getTextHyphenated());
    }

    /**
     * tests isFinanceNode();
     * @return void
     */
    public function testIsFinanceNode(): void
    {
        $hitch = [
            'textnodeId' => 12,
            'description' => 'foobar',
            'status' => Textnode::HITCH_STATUS_ACTIVE,
        ];
        self::assertTrue($this->textnode->isFinanceNode());
        $this->textnode->appendHitch($hitch);
        self::assertFalse($this->textnode->isFinanceNode());
    }

    /**
     * tests clearHitches() method
     * @return void
     */
    public function testClearHitches(): void
    {
        $hitch1 = [];
        $hitch1['textnodeId'] = 'Id1';
        $hitch1['description'] = 'Abort.';
        $hitch1['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $hitch2 = [];
        $hitch2['textnodeId'] = 'Id2';
        $hitch2['description'] = 'Abort.';
        $hitch2['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $hitch3 = [];
        $hitch3['textnodeId'] = 'Id3';
        $hitch3['description'] = 'Abort.';
        $hitch3['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $this->textnode->appendHitch($hitch1);
        $this->textnode->appendHitch($hitch2);
        $this->textnode->appendHitch($hitch3);
        self::assertEquals(3, $this->textnode->getHitchCount());

        $this->textnode->clearHitches();
        self::assertEquals(0, $this->textnode->getHitchCount());
    }

    /**
     * @return void
     */
    public function testSetParentTextnode(): void
    {
        /** @var $parentTextnodeMock \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $parentTextnodeMock = $this->createMock(Textnode::class);
        $this->textnode->setParentTextnode($parentTextnodeMock);
        $returnedParentTextnode = $this->textnode->getParentTextnode();
        self::assertSame($parentTextnodeMock, $returnedParentTextnode);
    }
}
