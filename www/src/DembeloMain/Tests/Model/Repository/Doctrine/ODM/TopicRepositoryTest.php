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
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var TopicRepository
     */
    private $repository;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->dm = static::$kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $collection = $this->dm->getDocumentCollection(Topic::class);
        $collection->remove(array());

        $this->repository = $this->dm->getRepository('DembeloMain:Topic');
    }

    /**
     * Test find topics with status active
     */
    public function testFindByStatusActiveWithoutTopics()
    {
        $foundTopics = $this->repository->findByStatusActive();
        $this->assertEquals([], $foundTopics);
    }

    /**
     * Test find topics with status active
     */
    public function testFindByStatusActiveWithTopics()
    {
        $topic1 = new Topic();
        $topic1->setName('topic1');
        $topic1->setStatus(Topic::STATUS_ACTIVE);
        $this->repository->save($topic1);

        $topic2 = new Topic();
        $topic2->setName('topic2');
        $topic2->setStatus(Topic::STATUS_INACTIVE);
        $this->repository->save($topic1);

        $foundTopics = $this->repository->findByStatusActive();
        $this->assertEquals(1, count($foundTopics));
    }
}
