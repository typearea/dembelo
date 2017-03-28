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

/**
 * Class ReadpathTest
 * @package DembeloMain\Tests\Model
 */
class ReadpathTest extends WebTestCase
{
    /**
     * tests the saving of a readpath node without a given user
     */
    public function testStoreReadpathWithoutUser()
    {
        $textnodeMock = $this->getTextnodeMock();
        $readpathRepositoryMock = $this->getReadpathRepositoryMock();
        $readpathRepositoryMock->expects($this->never())
            ->method('save');

        $readpath = new Readpath($readpathRepositoryMock);
        $readpath->storeReadpath($textnodeMock);
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

        $readpath = new Readpath($readpathRepositoryMock);
        $readpath->storeReadpath($textnodeMock, $userMock);
    }

    private function getTextnodeMock()
    {
        $textnode = $this->getMockBuilder(Textnode::class)->getMock();

        return $textnode;
    }

    private function getReadpathRepositoryMock()
    {
        $readpath = $this->getMockBuilder(ReadPathRepository::class)->disableOriginalConstructor()->getMock();

        return $readpath;
    }

    private function getUserMock()
    {
        $user = $this->getMockBuilder(User::class)->getMock();

        return $user;
    }
}