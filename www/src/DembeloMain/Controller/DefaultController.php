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
use DembeloMain\Document\Textnode;
use Symfony\Component\HttpFoundation\Response;
use Hyphenator\Core as Hyphenator;
use DembeloMain\Document\Readpath;

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

        if ($authorizationChecker->isGranted('ROLE_USER')) {
            $user = $tokenStorage->getToken()->getUser();

            $textnodeId = $user->getCurrentTextnode();

            if (!is_null($textnodeId)) {
                /**
                 * @todo Redirect to textnode, if the user browsed the index page directly (without
                 *     navigating to the index page via the menu) with an active user session resulting
                 *     from the "remember me" (cookie based) option. If the latter case isn't identified
                 *     correctly, the index page may always redirect to a textnode and wouldn't be
                 *     browsable any more.
                 */
            }
        }

        $repository = $mongo->getRepository('DembeloMain:Topic');
        $topics = $repository->findByStatus(Topic::STATUS_ACTIVE);

        if (!empty($topics)) {
            return $this->render('DembeloMain::default/index.html.twig', array('topics' => $topics));
        }

        return $this->render('DembeloMain::default/index.html.twig');
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
        $textnodes = null;

        /* @var $authorizationChecker \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        /* @var $tokenStorage Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        $mongo = $this->get('doctrine_mongodb');

        if (!$authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $dm = $mongo->getManager();
        $user = $tokenStorage->getToken()->getUser();
        $user->setLastTopicId($topicId);
        $dm->persist($user);
        $dm->flush();

        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnode = $repository->createQueryBuilder()
            ->field('topicId')->equals(new \MongoId($topicId))
            ->field('status')->equals(Textnode::STATUS_ACTIVE)
            ->field('access')->equals(true)
            ->getQuery()->getSingleResult();

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$topicId.'\' found, while the user was logged in, but without current textnode ID set.');
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
        $authorizationChecker = $this->get('security.authorization_checker');
        if (!$authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

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

        if (!$authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }
        $user = $tokenStorage->getToken()->getUser();

        $dm = $mongo->getManager();
        $user->setCurrentTextnode($textnodeId);
        $dm->persist($user);

        $oldReadpathItem = $dm->createQueryBuilder('DembeloMain:Readpath')
            ->field('userId')->equals(new \MongoId($user->getId()))
            ->sort('timestamp', 'desc')
            ->getQuery()
            ->getSingleResult();

        if (is_null($oldReadpathItem) || $oldReadpathItem->getTextnodeId() !== $textnodeId) {
            $readpath = new Readpath();
            $readpath->setUserId($user->getId());
            $readpath->setTextnodeId($textnodeId);
            $readpath->setTimestamp(new \MongoDate(time()));
            if (!is_null($oldReadpathItem)) {
                $readpath->setPreviousTextnodeId($oldReadpathItem->getTextnodeId());
            }
            $dm->persist($readpath);
        } else {
            $oldReadpathItem->setTimestamp(time());
            $dm->persist($oldReadpathItem);
        }

        $dm->flush();

        $textnode = $textnodes[0];

        $hyphenator = new Hyphenator();
        $hyphenator->registerPatterns('de');
        $hyphenator->setHyphen('&shy;');

        return $this->render(
            'DembeloMain::default/read.html.twig',
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

    /**
     * @Route("/reload/", name="reload")
     *
     * @return string
     *
     * @todo behaviour of left main menu button:
     *       if access node, then jump to homepage
     *       if not access node, them jump to another access node
     *       if no other access node available, then jump to homepage
     */
    public function reloadAction()
    {
        $mongo = $this->get('doctrine_mongodb');

        /* @var $authorizationChecker \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        /* @var $tokenStorage Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        if (!$authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }
        $user = $tokenStorage->getToken()->getUser();
        $currentTextnodeId = $user->getCurrentTextnode();
        $textnodeRepository = $mongo->getRepository('DembeloMain:Textnode');
        $textnodes = $textnodeRepository->findBy(
            array(
                'id' => new \MongoId($currentTextnodeId),
            )
        );
        $textnode = $textnodes[0];
        // find topic from readpath and show another access node
        $repository = $mongo->getRepository('DembeloMain:Textnode');
        $textnode = $repository->createQueryBuilder()
            ->field('topicId')->equals(new \MongoId($user->getLastTopicId()))
            ->field('status')->equals(Textnode::STATUS_ACTIVE)
            ->field('access')->equals(true)
            ->field('textnodeId')->notEqual($textnode->getId())
            ->getQuery()->getSingleResult();

        return $this->redirectToRoute('text', array('textnodeId' => $textnode->getId()));
    }

    /**
     * @Route("/imprint", name="imprint")
     *
     * @return string
     */
    public function imprintAction()
    {
        return $this->render('DembeloMain::default/imprint.html.twig');
    }
}
