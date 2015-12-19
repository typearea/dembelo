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
use Symfony\Component\HttpFoundation\Response;
use Hyphenator\Core as Hyphenator;

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

        /* @var $authorizationChecker \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        /* @var $tokenStorage Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
        $tokenStorage = $this->get('security.token_storage');

        $arguments = array();

        if ($authorizationChecker->isGranted('ROLE_USER')) {
            $user = $tokenStorage->getToken()->getUser();

            $textnodeId = $user->getCurrentTextnode();

            if (!is_null($textnodeId)) {
                $repository = $mongo->getRepository('DembeloMain:Textnode');
                $textnodes = $repository->findBy(array(
                    'id' => new \MongoId($textnodeId),
                    'status' => Textnode::STATUS_ACTIVE,
                ));

                if (!empty($textnodes)) {
                    $arguments['bookmarks'] = array($textnodes[0]);
                }
            }
        }

        $repository = $mongo->getRepository('DembeloMain:Topic');
        $topics = $repository->findByStatus(Topic::STATUS_ACTIVE);

        if (!empty($topics)) {
            $arguments['topics'] = $topics;
        }

        return $this->render('default/index.html.twig', $arguments);
    }

    /**
     * @Route("/themenfeld/{topicId}", name="themenfeld")
     *
     * @param string $topicId Topic ID from URL
     *
     * @return string
     */
    public function readTopicAction($topicId)
    {
        $mongo = $this->get('doctrine_mongodb');

        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnode = $repository->createQueryBuilder()
            ->field('topicId')->equals(new \MongoId($topicId))
            ->field('status')->equals(Textnode::STATUS_ACTIVE)
            ->field('access')->equals(true)
            ->getQuery()->getSingleResult();

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$topicId.'\' found.');
        }

        return $this->redirectToRoute('text', array('textnodeId' => $textnode->getId()));
    }

    /**
     * @Route("/text/{textnodeId}", name="text")
     *
     * @param string $textnodeId Textnode ID from URL
     *
     * @return string
     */
    public function readTextnodeAction($textnodeId)
    {
        $mongo = $this->get('doctrine_mongodb');

        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnodes = $repository->findBy(
            array(
                'id' => new \MongoId($textnodeId),
                'status' => Textnode::STATUS_ACTIVE,
            )
        );

        if (empty($textnodes)) {
            throw $this->createNotFoundException('No Textnode with ID \''.$textnodeId.'\' found.');
        }

        /* @var $authorizationChecker \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        /* @var $tokenStorage Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
        $tokenStorage = $this->get('security.token_storage');

        if ($authorizationChecker->isGranted('ROLE_USER')) {
            $user = $tokenStorage->getToken()->getUser();

            $dm = $mongo->getManager();
            $user->setCurrentTextnode($textnodeId);
            $dm->persist($user);
            $dm->flush();
        }

        $textnode = $textnodes[0];

        $hyphenator = new Hyphenator();
        $hyphenator->registerPatterns('de');
        $hyphenator->setHyphen('&shy;');

        return $this->render(
            'default/read.html.twig',
            array(
                'textnode' => $textnode,
                'hyphenated' => $hyphenator->hyphenate($textnode->getText()),
            )
        );
    }

    /**
     * @Route("/paywall/{textnodeId}/{hitchIndex}", name="paywall")
     *
     * @param string $textnodeId Textnode ID from URL
     * @param string $hitchIndex hitch index
     *
     * @return string
     */
    public function paywallAction($textnodeId, $hitchIndex)
    {
        $mongo = $this->get('doctrine_mongodb');

        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnodes = $repository->findBy(
            array(
                'id' => new \MongoId($textnodeId),
                'status' => Textnode::STATUS_ACTIVE,
            )
        );

        if (empty($textnodes)) {
            throw $this->createNotFoundException('No Textnode with ID \''.$textnodeId.'\' found.');
        }

        $textnode = $textnodes[0];
        $hitch = $textnode->getHitch($hitchIndex);

        $output = array(
            'url' => $this->generateUrl(
                'text',
                array(
                    'textnodeId' => $hitch['textnodeId'],
                )
            ),
        );

        return new Response(\json_encode($output));
    }
}
