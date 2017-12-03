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

namespace DembeloMain\Tests\Model;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\Doctrine\ODM\ReadPathRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Document\Readpath as ReadpathDocument;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class ReadpathTest
 */
class ReadpathTest extends WebTestCase
{
    /* @var Session */
    private $session;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->session = new Session(new MockArraySessionStorage());
    }

    /**
     * tests the saving of a readpath node without a given user
     */
    public function testStoreReadpathWithoutUser()
    {
        $textnodeMock1 = $this->getTextnodeMock();
        $textnodeMock1->expects($this->any())
            ->method('getId')
            ->willReturn('id1');
        $textnodeMock2 = $this->getTextnodeMock();
        $textnodeMock2->expects($this->any())
            ->method('getId')
            ->willReturn('id2');
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $readpathRepositoryMock->expects($this->never())
            ->method('save');

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $readpath->storeReadpath($textnodeMock1);
        $this->assertContains($textnodeMock1->getId(), $this->session->get('readpath'));

        $readpath->storeReadpath($textnodeMock2);
        $this->assertContains($textnodeMock1->getId(), $this->session->get('readpath'));
        $this->assertContains($textnodeMock2->getId(), $this->session->get('readpath'));
    }

    /**
     * tests the saving of readpath node
     */
    public function testStoreReadpathWithUser()
    {
        $textnodeMockId = 'textnodeId';
        $userMockId = 'userId';

        $textnodeMock = $this->getTextnodeMock();
        $userMock = $this->getUserMock();
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();

        $textnodeMock->expects($this->any())
            ->method('getId')
            ->willReturn($textnodeMockId);

        $userMock->expects($this->any())
            ->method('getId')
            ->willReturn($userMockId);

        $readpathRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (ReadpathDocument $readpathDocument) use ($textnodeMockId, $userMockId) {
                $this->assertInstanceOf(ReadpathDocument::class, $readpathDocument);
                $this->assertEquals($textnodeMockId, $readpathDocument->getTextnodeId());
                $this->assertEquals($userMockId, $readpathDocument->getUserId());
                $this->assertInstanceOf(\MongoDate::class, $readpathDocument->getTimestamp());
                $this->assertLessThanOrEqual(1, abs($readpathDocument->getTimestamp()->sec-time()));
            });

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $readpath->storeReadpath($textnodeMock, $userMock);

        $this->assertFalse($this->session->has('readpath'));
    }

    /**
     * tests getCurrentTextnodeId() for Session without readpath
     */
    public function testGetCurrentTextnodeIdForSessionWithoutReadpath()
    {
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $returnValue = $readpath->getCurrentTextnodeId();
        $this->assertNull($returnValue);
    }

    /**
     * tests getCurrentTextnodeId for session with readpath
     */
    public function testGetCurrentTextnodeIdForSessionWithReadpath()
    {
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $this->session->set('readpath', ['id1']);

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $returnValue = $readpath->getCurrentTextnodeId();
        $this->assertEquals('id1', $returnValue);
    }

    /**
     * tests getCurrentTextnodeId for session with multiple readpath
     */
    public function testGetCurrentTextnodeIdForSessionWithMultipleReadpath()
    {
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $this->session->set('readpath', ['id1', 'id2']);

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $returnValue = $readpath->getCurrentTextnodeId();
        $this->assertEquals('id2', $returnValue);
    }

    /**
     * tests getCurrentTextnodeId for usser without readpath
     */
    public function testGetCurrentTextnodeIdForUserWithoutReadpath()
    {
        $userMockId = 'userId';

        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $readpathRepositoryMock->expects($this->once())
            ->method('getCurrentTextnodeIdForUser')
            ->willReturn(null);

        $userMock = $this->getUserMock();
        $userMock->expects($this->any())
            ->method('getId')
            ->willReturn($userMockId);

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $returnValue = $readpath->getCurrentTextnodeId($userMock);
        $this->assertNull($returnValue);
    }

    /**
     * tests getCurrentTextnodeId for user with readpath
     */
    public function testGetCurrentTextnodeIdForUserWithReadpath()
    {
        $userMockId = 'userId';

        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $readpathRepositoryMock->expects($this->once())
            ->method('getCurrentTextnodeIdForUser')
            ->willReturn('someId');

        $userMock = $this->getUserMock();
        $userMock->expects($this->any())
            ->method('getId')
            ->willReturn($userMockId);

        $readpath = new Readpath($readpathRepositoryMock, $this->session);
        $returnValue = $readpath->getCurrentTextnodeId($userMock);
        $this->assertEquals('someId', $returnValue);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getTextnodeMock()
    {
        $textnode = $this->getMockBuilder(Textnode::class)->getMock();

        return $textnode;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getReadpathRepositoryMock()
    {
        $readpath = $this->getMockBuilder(ReadPathRepository::class)
            ->setMethods(['save', 'getCurrentTextnodeIdForUser'])
            ->disableOriginalConstructor()
            ->getMock();

        return $readpath;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getUserMock()
    {
        $user = $this->getMockBuilder(User::class)->getMock();

        return $user;
    }
}
