<?php

/* Copyright (C) 2015-2017 Michael Giesler, Stephan Kreutzer
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function readTopicAction($topicId)
    {
        if ($this->container->get('app.feature_toggle')->hasFeature('login_needed') && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $userRepository = $this->get('app.model_repository_user');
            $user->setLastTopicId($topicId);
            $userRepository->save($user);
        }

        $textnodeRepository = $this->get('app.model_repository_textNode');

        $textnode = $textnodeRepository->getTextnodeToRead($topicId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$topicId.'\' found.');
        }

        if ($textnode->isFinanceNode()) {
            return $this->redirectToRoute('financenode', ['textnodeArbitraryId' => $textnode->getArbitraryId()]);
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

        $textnodeRepository = $this->get('app.model_repository_textNode');
        $textnode = $textnodeRepository->findOneActiveByArbitraryId($textnodeArbitraryId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with arbitrary ID \''.$textnode->getArbitraryId().'\' found.');
        }

        if ($textnode->isFinanceNode()) {
            return $this->redirectToRoute('financenode', ['textnodeArbitraryId' => $textnode->getArbitraryId()]);
        }

        $user = $this->getUser();

        $this->get('app.readpath')->storeReadPath($textnode, $user);
        $this->get('app.favoriteManager')->setFavorite($textnode, $user);

        if ($user instanceof User) {
            $this->get('app.model_repository_user')->save($user);
        }

        $hitches = [];

        for ($i = 0; $i < $textnode->getHitchCount(); ++$i) {
            $hitch = $textnode->getHitch($i);
            $hitchedTextnode = $this->getTextnodeForTextnodeId($hitch['textnodeId']);
            $hitches[] = [
                'index' => $i,
                'description' => $hitch['description'],
                'arbitraryId' => $hitchedTextnode->getArbitraryId(),
                'isFinanceNode' => $hitchedTextnode->isFinanceNode(),
            ];
        }

        return $this->render(
            'DembeloMain::default/read.html.twig',
            array(
                'textnode' => $textnode,
                'hitches' => $hitches,
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
        $hitchedTextnode = $this->getTextnodeForHitchIndex($textnodeId, $hitchIndex);

        $output = array(
            'url' => $this->generateUrl(
                'text',
                array(
                    'textnodeArbitraryId' => $hitchedTextnode->getArbitraryId(),
                )
            ),
        );

        return new Response(\json_encode($output));
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

    private function getTextnodeForHitchIndex($textnodeId, $hitchIndex)
    {
        $textnodeRepository = $this->get('app.model_repository_textNode');

        $textnode = $textnodeRepository->findOneActiveById($textnodeId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with ID \''.$textnodeId.'\' found.');
        }

        $hitch = $textnode->getHitch($hitchIndex);

        return $this->getTextnodeForTextnodeId($hitch['textnodeId']);
    }

    private function getTextnodeForTextnodeId($textnodeId)
    {
        $textnodeRepository = $this->get('app.model_repository_textNode');
        $linkedTextnode = $textnodeRepository->findOneActiveById($textnodeId);

        return $linkedTextnode;
    }
}
