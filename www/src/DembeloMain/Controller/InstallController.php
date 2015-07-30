<?php

/**
 * This file is part of the Dembelo.
 *
 * (c) Michael Giesler <michael@horsemen.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DembeloMain
 * @author Michael Giesler <michael@4horsemen.de>
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
            $user = new User;
            $user->setEmail('michael@4horsemen.de');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $dm->persist($user);
        }

        $user = $userRepository->findOneByEmail('vistin@4horsemen.de');

        if (is_null($user)) {
            $user = new User;
            $user->setEmail('vistin@4horsemen.de');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $dm->persist($user);
        }

        $dm->flush();

        return new Response('Installation fertig!');
    }
}
