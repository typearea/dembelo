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

namespace DembeloMain\Controller\Dashboard;

use DembeloMain\Document\User;
use DembeloMain\Model\FavoriteManager;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class DefaultController
 * @Route(service="app.controller_dashboard")
 */
class DefaultController extends Controller
{
    /**
     * @var TopicRepositoryInterface
     */
    private $topicRepository;

    /**
     * @var FavoriteManager
     */
    private $favoriteManager;

    /**
     * @var Templating
     */
    private $templating;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * DefaultController constructor.
     * @param TopicRepositoryInterface $topicRepository
     * @param FavoriteManager $favoriteManager
     * @param Templating $templating
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TopicRepositoryInterface $topicRepository, FavoriteManager $favoriteManager, Templating $templating, TokenStorage $tokenStorage)
    {
        $this->topicRepository = $topicRepository;
        $this->favoriteManager = $favoriteManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/", name="mainpage")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $topics = $this->topicRepository->findBy([], ['sortKey' => 'ASC'], 8);
        $favorites = [];

        foreach ($topics as $topic) {
            $favoriteId = $this->favoriteManager->getFavorite($topic, $this->getUser());
            if (null === $favoriteId) {
                $favorites[$topic->getId()] = false;
            } else {
                $favorites[$topic->getId()] = $favoriteId;
            }
        }

        return $this->templating->renderResponse(
            'DembeloMain::dashboard/index.html.twig',
            [
                'topics' => $topics,
                'favorites' => $favorites,
            ]
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
}
