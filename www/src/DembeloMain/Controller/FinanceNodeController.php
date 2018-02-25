<?php
/* Copyright (C) 2017 Michael Giesler
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

use DembeloMain\Document\User;
use DembeloMain\Model\FeatureToggle;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class FinanceNodeController
 * @Route(service="app.controller_financenode")
 */
class FinanceNodeController extends Controller
{
    /**
     * @var Templating
     */
    private $templating;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var Readpath
     */
    private $readpath;

    /**
     * @var FeatureToggle
     */
    private $featureToggle;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var Router
     */
    private $router;

    /**
     * FinanceNodeController constructor.
     *
     * @param Templating                    $templating
     * @param TokenStorage                  $tokenStorage
     * @param Readpath                      $readpath
     * @param FeatureToggle                 $featureToggle
     * @param TextNodeRepositoryInterface   $textNodeRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Router                        $router
     */
    public function __construct(Templating $templating, TokenStorage $tokenStorage, Readpath $readpath, FeatureToggle $featureToggle, TextNodeRepositoryInterface $textNodeRepository, AuthorizationCheckerInterface $authorizationChecker, Router $router)
    {
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->readpath = $readpath;
        $this->featureToggle = $featureToggle;
        $this->textnodeRepository = $textNodeRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
    }

    /**
     * @Route("/collect/{textnodeArbitraryId}", name="financenode")
     *
     * @param string $textnodeArbitraryId arbitrary ID of textnode
     *
     * @return Response
     */
    public function showAction(string $textnodeArbitraryId): Response
    {
        if ($this->featureToggle->hasFeature('login_needed') && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login_route');
        }

        $textnode = $this->textnodeRepository->findOneActiveByArbitraryId($textnodeArbitraryId);

        if (null === $textnode) {
            return $this->redirectToRoute('mainpage');
        }

        $user = $this->getUser();

        $this->readpath->storeReadpath($textnode, $user);

        return $this->templating->renderResponse(
            'DembeloMain::financenode/show.html.twig'
        );
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
}
