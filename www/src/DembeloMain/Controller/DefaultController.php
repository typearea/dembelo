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

use DembeloMain\Document\Readpath;
use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use Hyphenator\Core as Hyphenator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
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

        $mongo = $this->get('doctrine_mongodb');

        if ($this->container->get('app.feature_toggle')->hasFeature('login_needed') && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $dm = $mongo->getManager();
        $user = $this->getUser();
        if ($user instanceof User) {
            $user->setLastTopicId($topicId);
            $dm->persist($user);
            $dm->flush();
        }

        $repository = $mongo->getRepository('DembeloMain:Textnode');

        /* @var $textnode Textnode */
        $textnode = $repository->createQueryBuilder()
            ->field('topicId')->equals(new \MongoId($topicId))
            ->field('status')->equals(Textnode::STATUS_ACTIVE)
            ->field('access')->equals(true)
            ->getQuery()->getSingleResult();

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$topicId.'\' found, while the user was logged in, but without current textnode ID set.');
        }

        return $this->redirectToRoute('text', array('textnodeArbitraryId' => $textnode->getArbitraryId()));
    }

    /**
     * @Route("/text/{textnodeArbitraryId}", name="text")
     *
     * @param string $textnodeArbitraryId Textnode arbitrary ID from URL
     *
     * @return string
     */
    public function readTextnodeAction($textnodeArbitraryId)
    {
        if ($this->container->get('app.feature_toggle')->hasFeature('login_needed') && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $mongo = $this->get('doctrine_mongodb');

        $textnodeRepository = $this->get('app.model_repository_textNode');
        $textnode = $textnodeRepository->findOneActiveByArbitraryId($textnodeArbitraryId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with arbitrary ID \''.$textnodeArbitraryId.'\' found.');
        }

        $user = $this->getUser();

        if ($user instanceof User) {
            $dm = $mongo->getManager();
            $user->setCurrentTextnode($textnode->getId());
            $dm->persist($user);

            $oldReadpathItem = $dm->createQueryBuilder('DembeloMain:Readpath')
                ->field('userId')->equals(new \MongoId($user->getId()))
                ->sort('timestamp', 'desc')
                ->getQuery()
                ->getSingleResult();

            if (is_null($oldReadpathItem) || $oldReadpathItem->getTextnodeId() !== $textnode->getId()) {
                $readpath = new Readpath();
                $readpath->setUserId($user->getId());
                $readpath->setTextnodeId($textnode->getId());
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
        }

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
        $textnodeRepository = $this->get('app.model_repository_textNode');

        $textnode = $textnodeRepository->findOneActiveById($textnodeId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with ID \''.$textnodeId.'\' found.');
        }

        $hitch = $textnode->getHitch($hitchIndex);
        $linkedTextnode = $textnodeRepository->findOneActiveById($hitch['textnodeId']);

        $output = array(
            'url' => $this->generateUrl(
                'text',
                array(
                    'textnodeArbitraryId' => $linkedTextnode->getArbitraryId(),
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
        if ($this->container->get('app.feature_toggle')->hasFeature('login_needed') && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnodeRepository = $this->get('app.model_repository_textNode');

        $user = $this->getUser();
        if ($user instanceof User) {
            $currentTextnodeId = $user->getCurrentTextnode();
            $textnode = $textnodeRepository->createQueryBuilder()
                ->field('topicId')->equals(new \MongoId($user->getLastTopicId()))
                ->field('status')->equals(Textnode::STATUS_ACTIVE)
                ->field('access')->equals(true)
                ->field('textnodeId')->notEqual($currentTextnodeId)
                ->getQuery()->getSingleResult();
        } else {
            $textnode = $textnodeRepository->createQueryBuilder()
                ->field('status')->equals(Textnode::STATUS_ACTIVE)
                ->field('access')->equals(true)
                ->getQuery()->getSingleResult();
        }

        return $this->redirectToRoute('text', array('textnodeArbitraryId' => $textnode->getArbitraryId()));
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
