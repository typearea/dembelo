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


/**
 * @package DembeloMain
 */

namespace DembeloMain\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DembeloMain\Document\Topic;
use DembeloMain\Document\Story;
use DembeloMain\Document\Textnode;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="mainpage")
     *
     * @return string
     */
    public function indexAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        $connection = $mongo->getConnection();

        if (!$connection->getMongo()) {
            $connection->connect();
        }

        $repository = $mongo->getRepository('DembeloMain:Topic');
        $topics = $repository->findByStatus(Topic::STATUS_ACTIVE);

        if (!is_null($topics)) {
            if (count($topics) > 0) {
                return $this->render('default/index.html.twig', array('topics' => $topics));
            }
        }

        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/themenfeld/{themeId}", name="themenfeld")
     *
     * @param string $themeId theme ID from URL
     *
     * @return string
     */
    public function readAction($themeId)
    {
        $textnodes = null;

        /* @var $authorizationChecker \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        /* @var $tokenStorage Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        $mongo = $this->get('doctrine_mongodb');

        if ($authorizationChecker->isGranted('ROLE_USER')) {
            $user = $tokenStorage->getToken()->getUser();

            $textnodeId = $user->getCurrentTextnode($themeId);

            if (is_null($textnodeId)) {
                $repository = $mongo->getRepository('DembeloMain:Textnode');
                $textnodes = $repository->findBy(array(
                    'topic_id' => new \MongoId($themeId),
                    'status' => Textnode::STATUS_ACTIVE,
                    'type' => Textnode::TYPE_INTRODUCTION,
                ));

                if (empty($textnodes)) {
                    throw $this->createNotFoundException('No Textnode for Topic \''.$themeId.'\' found.');
                }

                $dm = $mongo->getManager();
                $user->setCurrentTextnode($themeId, $textnodes[0]->getId());
                $dm->persist($user);

                return $this->render('default/read.html.twig', array('textnodeText' => $textnodes[0]->getText()));
            } else {
                $repository = $mongo->getRepository('DembeloMain:Textnode');
                $textnodes = $repository->findBy(array(
                    'id' => new \MongoId($textnodeId),
                    'status' => Textnode::STATUS_ACTIVE,
                ));

                if (empty($textnodes)) {
                    throw $this->createNotFoundException('No Textnode for Topic \''.$themeId.'\' found.');
                }

                return $this->render('default/read.html.twig', array('textnodeText' => $textnodes[0]->getText()));
            }
        }

        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnodes = $repository->findBy(
            array(
                'story_id' => new \MongoId($themeId),
                'status' => Textnode::STATUS_ACTIVE,
                'type' => Textnode::TYPE_INTRODUCTION,
            )
        );

        if (empty($textnodes)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$themeId.'\' found.');
        }

        return $this->render('default/read.html.twig', array('textnodeText' => $textnodes[0]->getText()));

    }
}
