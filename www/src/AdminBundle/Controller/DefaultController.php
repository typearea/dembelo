<?php

/* Copyright (C) 2015 Michael Giesler
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
 * @package AdminBundle
 */

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use StdClass;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_mainpage")
     *
     * @return string
     */
    public function indexAction()
    {
        return $this->render('AdminBundle::index.html.twig');
    }

    /**
     * @Route("/users", name="admin_users")
     *
     * @return String
     */
    public function usersAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:User');

        $users = $repository->findAll();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users AS $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->email = $user->getEmail();
            $obj->roles = join(', ', $user->getRoles());
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/authors", name="admin_authors")
     *
     * @return String
     */
    public function authorsAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Author');

        $users = $repository->findAll();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users AS $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->name = $user->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/topics", name="admin_topics")
     *
     * @return String
     */
    public function topicsAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Topic');

        $users = $repository->findAll();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users AS $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->name = $user->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/stories", name="admin_stories")
     *
     * @return String
     */
    public function storiesAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Story');

        $users = $repository->findAll();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users AS $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->name = $user->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }
}
