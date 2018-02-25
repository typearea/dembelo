<?php
/* Copyright (C) 2018 Michael Giesler
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

namespace DembeloMain\IntegrationTests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Readpath;
use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Document\Topic;
use DembeloMain\Document\User;

/**
 * Class WebTestCase
 */
class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var DocumentManager
     */
    private static $em;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        self::$em = self::$kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
    }

    /**
     * @return void
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function tearDown(): void
    {
        parent::tearDown();

        self::$em->createQueryBuilder(Importfile::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(Licensee::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(Readpath::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(Textnode::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(TextnodeHitch::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(Topic::class)->remove()->getQuery()->execute();
        self::$em->createQueryBuilder(User::class)->remove()->getQuery()->execute();
    }

    /**
     * @return DocumentManager
     */
    protected function getMongo(): DocumentManager
    {
        return self::$em;
    }
}
