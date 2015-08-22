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

        $connection->close();

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

        $securityContext = $this->get('security.context');

        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') === true ||
            $securityContext->isGranted('IS_AUTHENTICATED_FULLY') === true) {
            $user = $securityContext->getToken()->getUser();

            $textnodeId = $user->getCurrentTextnode($themeId);

            if (is_null($textnodeId)) {
                $mongo = $this->get('doctrine_mongodb');
                $connection = $mongo->getConnection();

                if (!$connection->getMongo()) {
                    $connection->connect();
                }

                $repository = $mongo->getRepository('DembeloMain:Story');
                $story = $repository->findOneBy(array('topic_id' => new \MongoId($themeId),
                                                      'status' => Story::STATUS_ACTIVE));

                if (!is_null($story)) {
                    $repository = $mongo->getRepository('DembeloMain:Textnode');
                    $textnodes = $repository->findBy(array('story_id' => new \MongoId($story->getId()),
                                                           'status' => Textnode::STATUS_ACTIVE,
                                                           'type' => Textnode::TYPE_INTRODUCTION));

                    if (!is_null($textnodes)) {
                        $textnodeId = $textnodes[0]->getId();
                        $textnodeText = $textnodes[0]->getText();

                        $dm = $mongo->getManager();
                        $user->setCurrentTextnode($themeId, $textnodeId);
                        $dm->persist($user);
                    }
                }

                $connection->close();
            }
            else {
                $mongo = $this->get('doctrine_mongodb');
                $connection = $mongo->getConnection();

                $repository = $mongo->getRepository('DembeloMain:Textnode');
                $textnodes = $repository->findBy(array('id' => new \MongoId($textnodeId),
                                                       'status' => Textnode::STATUS_ACTIVE));

                $connection->close();
            }
        }
        else {
            $mongo = $this->get('doctrine_mongodb');
            $connection = $mongo->getConnection();

            if (!$connection->getMongo()) {
                $connection->connect();
            }

            $repository = $mongo->getRepository('DembeloMain:Story');
            $story = $repository->findOneBy(array('topic_id' => new \MongoId($themeId),
                                                  'status' => Story::STATUS_ACTIVE));

            if (!is_null($story)) {
                $repository = $mongo->getRepository('DembeloMain:Textnode');
                $textnodes = $repository->findBy(array('story_id' => new \MongoId($story->getId()),
                                                       'status' => Textnode::STATUS_ACTIVE,
                                                       'type' => Textnode::TYPE_INTRODUCTION));
            }

            $connection->close();
        }

        if (!is_null($textnodes)) {
            if (count($textnodes) > 0) {
                return $this->render('default/read.html.twig', array('textnodeText' => $textnodes[0]->getText()));
            }
        }

        return $this->render('default/read.html.twig');
    }
}
