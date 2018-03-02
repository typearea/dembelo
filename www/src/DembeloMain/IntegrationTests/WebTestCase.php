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
use Symfony\Component\BrowserKit\Client;

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
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        self::$em = self::$kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->createAdminUser();
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
     * @param array $options
     * @param array $server
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static function createClient(array $options = [], array $server = [])
    {
        $server['PHP_AUTH_USER'] = 'admin@dembelo.tld';
        $server['PHP_AUTH_PW'] = 'dembelo';

        return parent::createClient($options, $server);
    }

    /**
     * @return DocumentManager
     */
    protected function getMongo(): DocumentManager
    {
        return self::$em;
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    private function createAdminUser(): void
    {
        $passwordEncoder = self::$kernel->getContainer()->get('security.password_encoder');
        $user = new User();
        $user->setStatus(1);
        $user->setEmail('admin@dembelo.tld');
        $user->setPassword($passwordEncoder->encodePassword($user, 'dembelo'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setStatus(1);
        $user->setMetadata(['created' => time(), 'updated' => time()]);
        self::$em->persist($user);
        self::$em->flush();
    }
}
