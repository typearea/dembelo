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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use StdClass;

/**
 * Class TopicController
 */
class TopicController extends Controller
{
    /**
     * @Route("/topics", name="admin_topics")
     *
     * @return String
     */
    public function topicsAction()
    {
        /* @var $repository TopicRepositoryInterface */
        $repository = $this->get('app.model_repository_topic');

        $topics = $repository->findBy([], ['sortKey' => 'ASC']);

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
     * @Route("/topics/uploadimage", name="admin_topics_image")
     *
     * @return Response
     */
    public function uploadImageAction()
    {
        $output = array();
        $file = $_FILES['upload'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $output['status'] = 'error';
            return new Response(\json_encode($output));
        }
        $directory = $this->container->getParameter('topic_image_directory');
        $filename = md5(uniqid().$file['name']);
        move_uploaded_file($file["tmp_name"], $directory.$filename);
        $output['imageFileName'] = $filename;
        $output['originalImageName'] = $file['name'];

        return new Response(\json_encode($output));
    }
}