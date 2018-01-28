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

use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use PHP_CodeSniffer\Generators\Text;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DembeloMain\Document\Textnode;

/**
 * Class DocumentTextnodeTest
 */
class TextnodeTest extends KernelTestCase
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
        /* @var $hitchMock \PHPUnit_Framework_MockObject_MockObject|TextnodeHitch */
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        /* @var $hitchMock \PHPUnit_Framework_MockObject_MockObject|TextnodeHitch */
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4578');
        $hitchMock->method('getDescription')->willReturn('Continue 2.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 2);
    }

    /**
     * Tests if a hitch can be correctly retrieved after its insertion.
     */
    public function testGetHitch()
    {
        /* @var $hitchMock \PHPUnit_Framework_MockObject_MockObject|TextnodeHitch */
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $result = $this->textnode->getHitch($this->textnode->getHitchCount() - 1);

        $this->assertFalse(null === $result);

        $this->assertEquals($result['textnodeId'], '55f5ab3708985c4b188b4577');
        $this->assertEquals($result['description'], 'Continue.');
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_ACTIVE);
    }

    /**
     * Tests if retrieving a hitch fails if a negative index is passed.
     */
    public function testGetHitchNegativeIndex()
    {
        $result = $this->textnode->getHitch(-1);

        $this->assertTrue(null === $result);
    }

    /**
     * Tests if retrieving a hitch fails if a too high index is passed.
     */
    public function testGetHitchTooHighIndex()
    {
        $result = $this->textnode->getHitch(0);

        $this->assertNull($result);
    }

    /**
     * Tests if the success of adding a hitch gets correctly signalled.
     */
    public function testAppendHitch()
    {
        /* @var $hitchMock \PHPUnit_Framework_MockObject_MockObject|TextnodeHitch */
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
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
            $hitchMock = $this->createMock(TextnodeHitch::class);
            $hitchMock->method('getTextnodeId')->willReturn($i);
            $hitchMock->method('getDescription')->willReturn('Continue.');
            $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

            if ($i < Textnode::HITCHES_MAXIMUM_COUNT) {
                $this->assertTrue($this->textnode->appendHitch($hitchMock), 'adding hitch no. '.$i);
            } else {
                $this->assertFalse($this->textnode->appendHitch($hitchMock), 'adding hitch no. '.$i);
            }
        }
    }

    /**
     * Tests if an old hitch can be replaced by a new hitch.
     */
    public function testSetHitch()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4578');
        $hitchMock->method('getDescription')->willReturn('Abort.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_INACTIVE);
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitchMock);

        $this->assertTrue($result);
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $result = $this->textnode->getHitch($this->textnode->getHitchCount() - 1);

        $this->assertFalse(null === $result);

        $this->assertEquals($result['textnodeId'], "55f5ab3708985c4b188b4578");
        $this->assertEquals($result['description'], "Abort.");
        $this->assertEquals($result['status'], Textnode::HITCH_STATUS_INACTIVE);
    }

    /**
     * Tests if updating a hitch fails if a negative index is passed.
     */
    public function testSetHitchNegativeIndex()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertFalse($this->textnode->setHitch(-1, $hitchMock));
    }

    /**
     * Tests if updating a hitch fails if a too high index is passed.
     */
    public function testSetHitchTooHighIndex()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertFalse($this->textnode->setHitch(1, $hitchMock));
    }

    /**
     * Tests if the attempt of updating a hitch fails if the textnodeId is missing.
     */
    public function testSetHitchWithoutTextnodeId()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getDescription')->willReturn('Abort.');
        $hitchMock->method('getTextnodeId')->willReturn(null);
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_INACTIVE);
        $this->expectException(\TypeError::class);
        $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitchMock);
    }

    /**
     * Tests if the attempt of updating a hitch fails if the description is missing.
     */
    public function testSetHitchWithoutDescription()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4578');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_INACTIVE);
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitchMock);

        $this->assertTrue($result);
    }

    /**
     * Tests if the attempt of updating a hitch fails if the status is missing.
     *
     * @return void
     */
    public function testSetHitchWithoutStatus(): void
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);
        $this->textnode->appendHitch($hitchMock);

        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4578');
        $hitchMock->method('getDescription')->willReturn('Abort.');
        $result = $this->textnode->setHitch($this->textnode->getHitchCount() - 1, $hitchMock);

        $this->assertTrue($result);
    }

    /**
     * Tests if the success of removing a hitch gets correctly signalled.
     */
    public function testRemoveHitch()
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
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
    public function testRemoveHitchNegativeIndex(): void
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $this->assertFalse($this->textnode->removeHitch(-1));
    }

    /**
     * Tests if removing a hitch fails if a too high index is passed.
     * @return void
     */
    public function testRemoveHitchTooHighIndex(): void
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $this->assertFalse($this->textnode->removeHitch(2));
    }

    /**
     * Tests if the correct hitch gets removed.
     * @return void
     */
    public function testRemoveHitchSpecific(): void
    {
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
        $this->assertEquals($this->textnode->getHitchCount(), 1);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4578');
        $hitchMock->method('getDescription')->willReturn('More.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_INACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
        $this->assertEquals($this->textnode->getHitchCount(), 2);

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4579');
        $hitchMock->method('getDescription')->willReturn('Abort.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->assertTrue($this->textnode->appendHitch($hitchMock));
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
        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('55f5ab3708985c4b188b4577');
        $hitchMock->method('getDescription')->willReturn('Continue.');
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        self::assertTrue($this->textnode->isFinanceNode());
        $this->textnode->appendHitch($hitchMock);
        self::assertFalse($this->textnode->isFinanceNode());
    }

    /**
     * tests clearHitches() method
     * @return void
     */
    public function testClearHitches(): void
    {
        $hitchMock1 = $this->createMock(TextnodeHitch::class);
        $hitchMock1->method('getTextnodeId')->willReturn('Id1');
        $hitchMock1->method('getDescription')->willReturn('Abort.');
        $hitchMock1->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $hitchMock2 = $this->createMock(TextnodeHitch::class);
        $hitchMock2->method('getTextnodeId')->willReturn('Id2');
        $hitchMock2->method('getDescription')->willReturn('Abort.');
        $hitchMock2->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $hitchMock3 = $this->createMock(TextnodeHitch::class);
        $hitchMock3->method('getTextnodeId')->willReturn('Id3');
        $hitchMock3->method('getDescription')->willReturn('Abort.');
        $hitchMock3->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $this->textnode->appendHitch($hitchMock1);
        $this->textnode->appendHitch($hitchMock2);
        $this->textnode->appendHitch($hitchMock3);
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

    /**
     * not exactly a unit test..
     * @todo establish a directory for integration tests
     */
    public function testReferences(): void
    {
        $kernel = self::bootKernel();
        /* @var $mongo \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $mongo = $kernel->getContainer()->get('doctrine_mongodb');
        $manager = $mongo->getManager();
        /* @var $textnodeRepository TextNodeRepository */
        $textnodeRepository = $mongo->getRepository(Textnode::class);

        $textnode = new Textnode();
        $textnode->setText('someText');
        $textnodeRepository->save($textnode);
        $textnodeId = $textnode->getId();
        self::assertNotNull($textnodeId);
        $manager->clear();
        self::assertFalse($manager->contains($textnode));
        $textnodeRefetched = $textnodeRepository->find($textnodeId);
        self::assertNotNull($textnodeRefetched);
        self::assertEquals('someText', $textnodeRefetched->getText());
        self::assertNull($textnodeRefetched->getParentTextnode());

        $parentTextnode = new Textnode();
        $parentTextnode->setText('parent textnode');
        $textnodeRepository->save($parentTextnode);
        $textnodeRefetched->setParentTextnode($parentTextnode);
        $textnodeRepository->save($textnodeRefetched);
        $manager->clear();
        $textnodeRefetched = $textnodeRepository->find($textnodeId);
        $parentTextnodeRefetched = $textnodeRefetched->getParentTextnode();
        self::assertNotNull($parentTextnodeRefetched);
        self::assertEquals('parent textnode', $parentTextnodeRefetched->getText());
    }
}
