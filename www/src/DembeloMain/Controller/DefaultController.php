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

use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use DembeloMain\Model\FavoriteManager;
use DembeloMain\Model\FeatureToggle;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class DefaultController
 * @Route(service="app.controller_default")
 */
class DefaultController extends Controller
{
    /**
     * @var FeatureToggle
     */
    private $featureToggle;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var Templating
     */
    private $templating;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Readpath
     */
    private $readpath;

    /**
     * @var FavoriteManager
     */
    private $favoriteManager;

    public function __construct(
        FeatureToggle $featureToggle,
        AuthorizationCheckerInterface $authorizationChecker,
        UserRepositoryInterface $userRepository,
        TextNodeRepositoryInterface $textNodeRepository,
        Templating $templating,
        Router $router,
        TokenStorage $tokenStorage,
        Readpath $readpath,
        FavoriteManager $favoriteManager
    ) {
        $this->featureToggle = $featureToggle;
        $this->authorizationChecker = $authorizationChecker;
        $this->userRepository = $userRepository;
        $this->textnodeRepository = $textNodeRepository;
        $this->templating = $templating;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->readpath = $readpath;
        $this->favoriteManager = $favoriteManager;
    }

    /**
     * @Route("/themenfeld/{topicId}", name="themenfeld")
     *
     * @param string $topicId Topic ID from URL
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function readTopicAction($topicId)
    {
        $textnodes = null;

        if ($this->featureToggle->hasFeature('login_needed') && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnode = $this->textnodeRepository->getTextnodeToRead($topicId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode for Topic \''.$topicId.'\' found.');
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $user->setLastTopicId($topicId);
            $this->userRepository->save($user);
        }

        if ($textnode->isFinanceNode()) {
            return $this->redirectToRoute('financenode', ['textnodeArbitraryId' => $textnode->getArbitraryId()]);
        }

        return $this->redirectToRoute('text', array('textnodeArbitraryId' => $textnode->getArbitraryId()));
    }

    protected function redirectToRoute($route, array $parameters = array(), $status = 302): RedirectResponse
    {
        $url = $this->router->generate($route, $parameters);
        return new RedirectResponse($url, $status);
    }

    protected function getUser(): ?User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
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
        if ($this->featureToggle->hasFeature('login_needed') && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnode = $this->textnodeRepository->findOneActiveByArbitraryId($textnodeArbitraryId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with arbitrary ID \''.$textnode->getArbitraryId().'\' found.');
        }

        if ($textnode->isFinanceNode()) {
            return $this->redirectToRoute('financenode', ['textnodeArbitraryId' => $textnode->getArbitraryId()]);
        }

        $user = $this->getUser();

        $this->readpath->storeReadPath($textnode, $user);
        $this->favoriteManager->setFavorite($textnode, $user);

        if ($user instanceof User) {
            $this->userRepository->save($user);
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

        return $this->templating->renderResponse(
            'DembeloMain::default/read.html.twig',
            [
                'textnode' => $textnode,
                'hitches' => $hitches,
            ]
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
     * @Route("/back", name="back")
     *
     * @return string
     */
    public function backAction()
    {
        // get current textnode from readpath
        $readpath = $this->get('app.readpath');
        $currentTextnode = $readpath->getCurrentTextnode();
        //$readpath->
        // if current textnode === access node => get other access node
        // if current textnode !== access node => get previous node
        $textnode = '';
        return $this->redirectToRoute('text', array('textnodeArbitraryId' => $textnode->getArbitraryId()));
    }

    /**
     * @Route("/imprint", name="imprint")
     *
     * @return string
     */
    public function imprintAction()
    {
        return $this->templating->renderResponse('DembeloMain::default/imprint.html.twig');
    }

    private function getTextnodeForHitchIndex($textnodeId, $hitchIndex): Textnode
    {
        $textnode = $this->textnodeRepository->findOneActiveById($textnodeId);

        if (is_null($textnode)) {
            throw $this->createNotFoundException('No Textnode with ID \''.$textnodeId.'\' found.');
        }

        $hitch = $textnode->getHitch($hitchIndex);

        return $this->getTextnodeForTextnodeId($hitch['textnodeId']);
    }

    private function getTextnodeForTextnodeId($textnodeId): Textnode
    {
        $linkedTextnode = $this->textnodeRepository->findOneActiveById($textnodeId);

        return $linkedTextnode;
    }
}
