<?php

/* Copyright (C) 2015 Michael Giesler <michael@horsemen.de>
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
 *
 * @package DembeloMain
 */

namespace DembeloMain\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use DembeloMain\Document\User;

/**
 * Class InstallController
 */
class InstallController extends Controller
{
    /**
     * @Route("/install", name="install")
     *
     * @return string
     */
    public function indexAction()
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $userRepository = $this->get('doctrine_mongodb')->getRepository('DembeloMain:User');

        $encoder = $this->container->get('security.password_encoder');


        $user = $userRepository->findOneByEmail('michael@4horsemen.de');

        if (is_null($user)) {
            $user = new User();
            $user->setEmail('michael@4horsemen.de');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $dm->persist($user);
        }

        $user = $userRepository->findOneByEmail('vistin@4horsemen.de');

        if (is_null($user)) {
            $user = new User();
            $user->setEmail('vistin@4horsemen.de');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $dm->persist($user);
        }

        $dm->flush();

        return new Response('Installation fertig!');
    }
}
