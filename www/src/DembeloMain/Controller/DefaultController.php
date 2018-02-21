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

declare(strict_types = 1);

namespace DembeloMain\Controller;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
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

    /**
     * DefaultController constructor.
     * @param FeatureToggle                 $featureToggle
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UserRepositoryInterface       $userRepository
     * @param TextNodeRepositoryInterface   $textNodeRepository
     * @param Templating                    $templating
     * @param Router                        $router
     * @param TokenStorage                  $tokenStorage
     * @param Readpath                      $readpath
     * @param FavoriteManager               $favoriteManager
     */
    public function __construct(FeatureToggle $featureToggle, AuthorizationCheckerInterface $authorizationChecker, UserRepositoryInterface $userRepository, TextNodeRepositoryInterface $textNodeRepository, Templating $templating, Router $router, TokenStorage $tokenStorage, Readpath $readpath, FavoriteManager $favoriteManager)
    {
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
     *
     * @throws NotFoundHttpException
     */
    public function readTopicAction($topicId): RedirectResponse
    {
        if ($this->featureToggle->hasFeature('login_needed') && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnode = $this->textnodeRepository->getTextnodeToRead($topicId);

        if (null === $textnode) {
            throw $this->createNotFoundException(sprintf('No Textnode for Topic \'%s\' found.', $topicId));
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

    /**
     * @Route("/text/{textnodeArbitraryId}", name="text")
     *
     * @param string $textnodeArbitraryId Textnode arbitrary ID from URL
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function readTextnodeAction($textnodeArbitraryId): Response
    {
        if ($this->featureToggle->hasFeature('login_needed') && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnode = $this->textnodeRepository->findOneActiveByArbitraryId($textnodeArbitraryId);

        if (null === $textnode) {
            throw $this->createNotFoundException(sprintf('No Textnode with arbitrary ID \'%s\' found.', $textnodeArbitraryId));
        }

        if ($textnode->isFinanceNode()) {
            return $this->redirectToRoute('financenode', ['textnodeArbitraryId' => $textnode->getArbitraryId()]);
        }

        $user = $this->getUser();

        $this->readpath->storeReadpath($textnode, $user);
        $this->favoriteManager->setFavorite($textnode, $user);

        if ($user instanceof User) {
            $this->userRepository->save($user);
        }

        $hitches = [];

        $childHitches = $textnode->getChildHitches();
        $index = 0;
        foreach ($childHitches as $childHitch) {
            $hitchedTextnode = $childHitch->getTargetTextnode();
            $hitches[] = [
                'index' => $index,
                'description' => $childHitch->getDescription(),
                'arbitraryId' => $hitchedTextnode->getArbitraryId(),
                'isFinanceNode' => $hitchedTextnode->isFinanceNode(),
            ];
            ++$index;
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
     * @return Response
     */
    public function paywallAction($textnodeId, $hitchIndex): Response
    {
        $hitchedTextnode = $this->getTextnodeForHitchIndex($textnodeId, $hitchIndex);

        $url = $this->router->generate('text', ['textnodeArbitraryId' => $hitchedTextnode->getArbitraryId()]);

        $output = [
            'url' => $url,
        ];

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/back", name="back")
     *
     * @return RedirectResponse
     */
    public function backAction(): RedirectResponse
    {
        $parentHitch = $this->getParentHitch();
        if (null === $parentHitch) {
            return $this->redirectToRoute('mainpage');
        }
        $parentTextnode = $parentHitch->getSourceTextnode();
        if ($parentTextnode->getAccess()) {
            return $this->redirectToRoute('themenfeld', ['topicId' => $parentTextnode->getTopicId()]);
        }
        return $this->redirectToRoute(
            'text',
            array('textnodeArbitraryId' => $parentTextnode->getArbitraryId())
        );
    }

    /**
     * @return TextnodeHitch|null
     */
    private function getParentHitch(): ?TextnodeHitch
    {
        $user = $this->getUser();
        $lastTextnodeId = $this->readpath->getCurrentTextnodeId($user);

        if (null === $lastTextnodeId) {
            return null;
        }
        $lastTextnode = $this->textnodeRepository->find($lastTextnodeId);
        if (null === $lastTextnode) {
            return null;
        }
        $parentHitches = $lastTextnode->getParentHitches();
        if ($parentHitches->isEmpty()) {
            return null;
        }

        return $parentHitches->first();
    }

    /**
     * @Route("/imprint", name="imprint")
     *
     * @return Response
     */
    public function imprintAction(): Response
    {
        return $this->templating->renderResponse('DembeloMain::default/imprint.html.twig');
    }

    /**
     * @param string $route
     * @param array  $parameters
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, array $parameters = array(), $status = 302): RedirectResponse
    {
        $url = $this->router->generate($route, $parameters);

        return new RedirectResponse($url, $status);
    }

    /**
     * @return User|null
     */
    protected function getUser(): ?User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * @param string $textnodeId
     * @param int    $hitchIndex
     *
     * @return Textnode|null
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getTextnodeForHitchIndex($textnodeId, $hitchIndex): ?Textnode
    {
        $textnode = $this->textnodeRepository->findOneActiveById($textnodeId);

        if (null === $textnode) {
            throw $this->createNotFoundException(sprintf('No Textnode with ID \'%s\' found.', $textnodeId));
        }

        /* @var $hitch TextnodeHitch */
        $hitch = $textnode->getChildHitches()->get($hitchIndex);

        return $hitch->getTargetTextnode();
    }
}
