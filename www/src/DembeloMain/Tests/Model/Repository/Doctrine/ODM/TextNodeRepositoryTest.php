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

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;

/**
 * Class TextNodeRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class TextNodeRepositoryTest extends AbstractRepositoryTest
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var TextNodeRepository
     */
    private $repository;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $collection = $this->em->getDocumentCollection(Textnode::class);
        $collection->remove(array());
        $collection = $this->em->getDocumentCollection(Importfile::class);
        $collection->remove(array());

        $this->repository = $this->em->getRepository('DembeloMain:Textnode');
    }

    /**
     * tests findByImportfileId with no textnodes to be found
     */
    public function testFindByImportfileIdWithNoTextnodes()
    {
        $importfile = $this->createImportfile();
        $textnodes = $this->repository->findByImportfileId($importfile->getId());

        $this->assertEquals([], $textnodes);
    }

    /**
     * tests findByImportfileId with one textnode to be found
     */
    public function testFindByImportfileIdWithOneTextnode()
    {
        $importfile = $this->createImportfile();

        $textnode = new Textnode();
        $textnode->setImportfileId($importfile->getId());
        $this->repository->save($textnode);

        $textnodes = $this->repository->findByImportfileId($importfile->getId());

        $this->assertEquals(1, count($textnodes));
        $this->assertEquals($importfile->getId(), $textnodes[0]->getImportfileId());
    }

    /**
     * tests findByImportfileId with a textnode not to be found
     */
    public function testFindByImportfileIdWithATextnodeOfAnotherImportfile()
    {
        $importfile = $this->createImportfile();

        $textnode1 = new Textnode();
        $textnode1->setImportfileId($importfile->getId());
        $this->repository->save($textnode1);

        $textnode2 = new Textnode();
        $this->repository->save($textnode2);

        $textnodes = $this->repository->findByImportfileId($importfile->getId());

        $this->assertEquals(1, count($textnodes));
        $this->assertEquals($importfile->getId(), $textnodes[0]->getImportfileId());
        $this->assertEquals($textnode1->getId(), $textnodes[0]->getId());
    }

    /**
     * tests findByTwineId with no textnodes to be found
     */
    public function testFindByTwineIdWithNoTextnodes()
    {
        $importfile = $this->createImportfile();
        $textnode = $this->repository->findByTwineId($importfile, 'twineId');

        $this->assertNull($textnode);
    }

    /**
     * tests findByTwineId with a textnode to be found
     */
    public function testFindByTwineIdWithTextnodes()
    {
        $importfile = $this->createImportfile();

        $textnode = new Textnode();
        $textnode->setImportfileId($importfile->getId());
        $textnode->setTwineId('twineId');
        $this->repository->save($textnode);

        $retVal = $this->repository->findByTwineId($importfile, 'twineId');

        $this->assertEquals($textnode, $retVal);
    }

    /**
     * tests findByTwineId with existing textnodes with another twineId
     */
    public function testFindByTwineIdWithTextnodesOfAnotherTwineId()
    {
        $importfile = $this->createImportfile();

        $textnode1 = new Textnode();
        $textnode1->setImportfileId($importfile->getId());
        $textnode1->setTwineId('twineId');
        $this->repository->save($textnode1);

        $textnode2 = new Textnode();
        $textnode2->setImportfileId($importfile->getId());
        $textnode2->setTwineId('twineId2');
        $this->repository->save($textnode2);

        $retVal = $this->repository->findByTwineId($importfile, 'twineId');

        $this->assertEquals($textnode1, $retVal);
    }

    /**
     * tests disableOrphanedNodes method
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testDisableOrphanedNodes()
    {
        $importfile = $this->createImportfile();

        $textnode1 = new Textnode();
        $textnode1->setImportfileId($importfile->getId());
        $textnode1->setStatus(Textnode::STATUS_ACTIVE);
        $this->repository->save($textnode1);

        $textnode2 = new Textnode();
        $textnode2->setImportfileId($importfile->getId());
        $textnode2->setStatus(Textnode::STATUS_ACTIVE);
        $this->repository->save($textnode2);

        $this->repository->disableOrphanedNodes($importfile, [$textnode1->getId()]);

        $cursor = $this->repository->createQueryBuilder()->find()->refresh()->getQuery()->execute();

        $this->assertEquals(2, count($cursor));

        foreach ($cursor as $c) {
            if ($c->getId() === $textnode1->getId()) {
                $this->assertEquals(Textnode::STATUS_ACTIVE, $c->getStatus());
            } elseif ($c->getId() === $textnode2->getId()) {
                $this->assertEquals(Textnode::STATUS_INACTIVE, $c->getStatus());
            } else {
                $this->assertFalse(true);
            }
        }
    }

    private function createImportfile()
    {
        $importfile = new Importfile();
        $importfile->setName('importfile');
        $this->em->getRepository('DembeloMain:Importfile')->save($importfile);

        return $importfile;
    }
}
