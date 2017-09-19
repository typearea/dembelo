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

use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use DembeloMain\Document\Readpath;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;

/**
 * Class ReadPathRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class ReadPathRepositoryTest extends AbstractRepositoryTest
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $em;

    /**
     * @var TextNodeRepository
     */
    private $repository;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $collection = $this->em->getDocumentCollection(Readpath::class);
        $collection->remove(array());
        $collection = $this->em->getDocumentCollection(User::class);
        $collection->remove(array());
        $collection = $this->em->getDocumentCollection(Textnode::class);
        $collection->remove(array());

        $this->repository = $this->em->getRepository('DembeloMain:Readpath');
    }

    /**
     * tests getCurrentTextnodeId for user returns null when readpath is empty
     */
    public function testGetCurrentTextnodeIdForUserReturnsNullWhenReadpathIsEmpty(): void
    {
        $user = $this->createUser();
        $returnValue = $this->repository->getCurrentTextnodeIdForUser($user);

        $this->assertNull($returnValue);
    }

    /**
     * tests getCurrentTextnodeId for user returning textnodeId
     */
    public function testGetCurrentTextnodeIdForUserReturnsTextnodeId(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $textnode1 = $this->createTextnode('a');
        $readpath1 = new Readpath();
        $readpath1->setUserId($user1->getId());
        $readpath1->setTextnodeId($textnode1->getId());
        $readpath1->setTimestamp(new \MongoDate(1000000));
        $this->repository->save($readpath1);

        $textnode2 = $this->createTextnode('b');
        $readpath2 = new Readpath();
        $readpath2->setUserId($user1->getId());
        $readpath2->setTextnodeId($textnode2->getId());
        $readpath2->setTimestamp(new \MongoDate(1000001));
        $this->repository->save($readpath2);

        $textnode3 = $this->createTextnode('c');
        $readpath3 = new Readpath();
        $readpath3->setUserId($user2->getId());
        $readpath3->setTextnodeId($textnode3->getId());
        $readpath3->setTimestamp(new \MongoDate(1000002));
        $this->repository->save($readpath3);

        $returnValue = $this->repository->getCurrentTextnodeIdForUser($user1);

        $this->assertEquals($textnode2->getId(), $returnValue);
    }

    private function createUser(): User
    {
        $user = new User();
        $this->em->getRepository('DembeloMain:User')->save($user);

        return $user;
    }

    private function createTextnode($arbitraryId): Textnode
    {
        $textnode = new Textnode();
        $textnode->setArbitraryId($arbitraryId);
        $this->em->getRepository('DembeloMain:Textnode')->save($textnode);

        return $textnode;
    }
}
