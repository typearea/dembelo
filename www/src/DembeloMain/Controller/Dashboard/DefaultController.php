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

use DembeloMain\Document\Textnode;
use DembeloMain\Model\FavoriteManager;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class DefaultController
 * @package DembeloMain\Controller\Dashboard
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="mainpage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $topicRepository = $this->get('app.model_repository_topic');
        $favoriteManager = $this->get('app.favoriteManager');

        $topics = $topicRepository->findBy([], array('sortKey' => 'ASC'), 8);
        $favorites = [];

        foreach ($topics as $topic) {
            $favoriteId = $favoriteManager->getFavorite($topic, $this->getUser());
            if (is_null($favoriteId)) {
                $favorites[$topic->getId()] = false;
            } else {
                $favorites[$topic->getId()] = $favoriteId;
            }
        }

        return $this->render(
            'DembeloMain::dashboard/index.html.twig',
            array(
                'topics' => $topics,
                'favorites' => $favorites,
            )
        );
    }
}
