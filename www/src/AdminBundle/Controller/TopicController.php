<?php
/* Copyright (C) 2016 Michael Giesler
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

namespace AdminBundle\Controller;

use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TopicController
 *
 * @Route(service="app.controller_admin_topic")
 */
class TopicController extends Controller
{

    /**
     * @var TopicRepositoryInterface
     */
    private $topicRepository;

    /**
     * TopicController constructor.
     * @param ContainerInterface       $container
     * @param TopicRepositoryInterface $topicRepository
     */
    public function __construct(ContainerInterface $container, TopicRepositoryInterface $topicRepository)
    {
        $this->container = $container;
        $this->topicRepository = $topicRepository;
    }

    /**
     * @Route("/topic/list", name="admin_topics")
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $filters = $request->query->get('filter');

        if (null === $filters) {
            $topics = $this->topicRepository->findBy([], ['sortKey' => 'ASC']);
        } else {
            $topics = $this->topicRepository->findFiltered($filters, ['sortKey', 'ASC']);
        }

        $output = array();
        /* @var $topic \DembeloMain\Document\Topic */
        foreach ($topics as $topic) {
            $item = [];
            $item['id'] = $topic->getId();
            $item['name'] = $topic->getName();
            $item['status'] = (String) $topic->getStatus();
            $item['sortKey'] = $topic->getSortKey();
            $item['originalImageName'] = $topic->getOriginalImageName();
            $output[] = $item;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/topicSuggest", name="admin_topic_suggest")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function topicSuggestAction(Request $request): Response
    {
        $filter = $request->query->get('filter');

        $searchString = $filter['value'];

        /* @var $topics \DembeloMain\Document\Topic[] */
        $topics = $this->topicRepository->findBy(array('name' => new \MongoRegex('/'.$searchString.'/')), null, 10);

        $output = [];
        foreach ($topics as $topic) {
            $output[] = array(
                'id' => $topic->getId(),
                'value' => $topic->getName(),
            );
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/topic/uploadimage", name="admin_topics_image")
     *
     * @return Response
     */
    public function uploadImageAction(): Response
    {
        $output = array();
        $file = $_FILES['upload'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $output['status'] = 'error';

            return new Response(\json_encode($output));
        }
        $directory = $this->getParameter('topic_image_directory');
        $filename = md5(uniqid().$file['name']);
        move_uploaded_file($file["tmp_name"], $directory.$filename);
        $output['imageFileName'] = $filename;
        $output['originalImageName'] = $file['name'];

        return new Response(\json_encode($output));
    }
}
