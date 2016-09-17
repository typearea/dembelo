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

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use Doctrine\MongoDB\ArrayIterator;

/**
 * Class TopicRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class TopicRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new TopicRepository($dm, $uow, $class);
        $topic = $repository->save(new Topic());

        $this->assertInstanceOf(Topic::class, $topic);
    }

    /**
     * Test find topics with status active
     */
    public function testFindByStatusActive()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();
        $documentPersister = $this->getDocumentPersisterMock();

        $collection = new ArrayIterator(array(new Topic()));
        $documentPersister->expects($this->once())->method('loadAll')->willReturn($collection);
        $uow->expects($this->once())->method('getDocumentPersister')->willReturn($documentPersister);

        $repository = new TopicRepository($dm, $uow, $class);
        $topics = $repository->findByStatusActive();

        $this->assertInstanceOf(Topic::class, $topics[0]);
    }
}
