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
use DembeloMain\Document\User;
use DembeloMain\Document\Author;
use DembeloMain\Document\Topic;
use DembeloMain\Document\Story;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

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
        $mainMenuData = [
            ['id' => "1", 'type' => "folder", 'value' => "Benutzer", 'css' => "folder_music"],
            ['id' => "2", 'type' => "folder", 'value' => "Lizenznehmer", 'css' => "folder_music"],
            ['id' => "3", 'type' => "folder", 'value' => "Autoren", 'css' => "folder_music"],
            ['id' => "4", 'type' => "folder", 'value' => "Themenfelder", 'css' => "folder_music"],
            ['id' => "5", 'type' => "folder", 'value' => "Geschichten", 'css' => "folder_music"],
        ];

        $jsonEncoder = new JsonEncoder();
        
        return $this->render('AdminBundle::index.html.twig', array('mainMenuData' => $jsonEncoder->encode($mainMenuData, 'json')));
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
        foreach ($users as $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->email = $user->getEmail();
            $obj->roles = join(', ', $user->getRoles());
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/licensees", name="admin_licensees")
     *
     * @return String
     */
    public function licenseesAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Licensee');

        $licensees = $repository->findAll();

        $output = array();
        /* @var $licensee \DembeloMain\Document\Licensee */
        foreach ($licensees as $licensee) {
            $obj = new StdClass();
            $obj->id = $licensee->getId();
            $obj->name = $licensee->getName();
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
        foreach ($users as $user) {
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
        foreach ($users as $user) {
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
        foreach ($users as $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->name = $user->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/save", name="admin_formsave")
     *
     * @param Request $request
     * @return String
     */
    public function formsaveAction(Request $request)
    {
        $params = $request->request->all();

        if (!isset($params['formtype']) || !in_array($params['formtype'], array('user', 'licensee', 'author', 'topic', 'story'))) {
            return new Response(\json_encode(array('error' => true)));
        }
        $formtype = ucfirst($params['formtype']);

        /* @var $mongo \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $mongo = $this->get('doctrine_mongodb');
        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager*/
        $dm = $mongo->getManager();

        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:'.$formtype);
        if (isset($params['id']) && $params['id'] == 'new') {
            $className = $repository->getClassName();
            $item = new $className();
        } else {
            $item = $repository->find($params['id']);
            if (is_null($item) || $item->getId() != $params['id']) {
                return new Response(\json_encode(array('error' => true)));
            }
        }
        foreach ($params as $param => $value) {
            if (in_array($param, array('id', 'formtype'))) {
                continue;
            }
            if ($param == 'password' && empty($value)) {
                continue;
            } elseif ($param == 'password') {
                $encoder = $this->get('security.password_encoder');
                $value = $encoder->encodePassword($item, $value);
            }
            $method = 'set'.ucfirst($param);
            $item->$method($value);
        }
        $dm->persist($item);
        $dm->flush();

        $output = array(
            'error' => false,
            'newId' => $item->getId(),
        );

        return new Response(\json_encode($output));

    }

    /**
     * @Route("/delete", name="admin_formdel")
     *
     * @param Request $request
     * @return String
     */
    public function formdelAction(Request $request)
    {
        $params = $request->request->all();

        if (!isset($params['formtype']) || !in_array($params['formtype'], array('user', 'author', 'topic', 'story'))) {
            return new Response(\json_encode(array('error' => true)));
        }

        $formtype = ucfirst($params['formtype']);

        /* @var $mongo \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $mongo = $this->get('doctrine_mongodb');
        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager*/
        $dm = $mongo->getManager();

        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:'.$formtype);
        if (!isset($params['id']) || empty($params['id'])) {
            return new Response(\json_encode(array('error' => true)));
        }

        $user = $repository->find($params['id']);
        if (is_null($user)) {
            return new Response(\json_encode(array('error' => true)));
        }
        $dm->remove($user);
        $dm->flush();

        return new Response(\json_encode(array('error' => false)));

    }
}
