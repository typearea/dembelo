<?php
/* Copyright (C) 2016 Michael Giesler
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

namespace DembeloMain\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DembeloMain\Document\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadSeleniumData
 * loads all data used for selenium test suite
 *
 * @package DembeloMain\DataFixtures\MongoDB
 */
class LoadSeleniumData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * sets the symfony container
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * loads all the data to the database
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $users = array(
            array(
                'email' => 'admin@dembelo.tld',
                'password' => 'dembelo',
                'roles' => array('ROLE_ADMIN'),
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => array('created' => time(), 'updated' => time()),
            ),
        );

        $this->installUsers($users, $manager);
    }

    private function installUsers(array $users, ObjectManager $manager)
    {
        $repository = $manager->getRepository('DembeloMain:User');

        $encoder = $this->container->get('security.password_encoder');

        if (!isset($this->dummyData['users'])) {
            $this->dummyData['users'] = array();
        }

        foreach ($users as $userData) {
            $user = $repository->findOneByEmail($userData['email']);

            if (is_null($user)) {
                $user = new User();
                $user->setEmail($userData['email']);
                $password = $encoder->encodePassword($user, $userData['password']);
                $user->setPassword($password);
                $user->setRoles($userData['roles']);
                $user->setGender($userData['gender']);
                $user->setSource($userData['source']);
                $user->setReason($userData['reason']);
                $user->setStatus($userData['status']);
                $user->setMetadata($userData['metadata']);

                $manager->persist($user);
                $manager->flush();
            }
        }
    }
}
