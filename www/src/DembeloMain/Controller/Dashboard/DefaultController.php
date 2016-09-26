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
    /** @var TextNodeRepositoryInterface */
    private $topicRepository;
    private $templating;

    /**
     * DefaultController constructor.
     * @param EngineInterface          $templating
     * @param TopicRepositoryInterface $topicRepository
     */
    public function __construct(EngineInterface $templating, TopicRepositoryInterface $topicRepository)
    {
        $this->templating = $templating;
        $this->topicRepository = $topicRepository;
    }

    /**
     * @Route("/", name="mainpage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'DembeloMain::dashboard/index.html.twig',
            array('topics' => $this->topicRepository->findByStatusActive())
        );
    }
}
