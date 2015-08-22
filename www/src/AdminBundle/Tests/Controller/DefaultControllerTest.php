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

namespace AdminBundle\Tests\Controller;

use DembeloMain\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AdminBundle\Controller\DefaultController;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
    private $container;
    private $repository;
    private $service;

    /**
     * tests the index action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/');


        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("login")')->count() > 0);
    }

    /**
     * tests controller's userAction with no users in db
     */
    public function testUserAction()
    {
        $this->loadMongoContainer();
        $this->repository->expects($this->once())
            ->method("findAll")
            ->will($this->returnValue(array()));

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->usersAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests controller's userAction with two users in db
     */
    public function testUserActionWithUsers()
    {
        $this->loadMongoContainer();
        $user1 = new User();
        $user1->setEmail('email1');
        $user1->setId('id1');
        $user1->setRoles('ROLE_ADMIN');
        $user2 = new User();
        $user2->setEmail('email2');
        $user2->setId('id2');
        $user2->setRoles('ROLE_USER');

        $userArray = array(
            $user1,
            $user2
        );
        $this->repository->expects($this->once())
            ->method("findAll")
            ->will($this->returnValue($userArray));


        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->usersAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"id1","email":"email1","roles":"ROLE_ADMIN"},{"id":"id2","email":"email2","roles":"ROLE_USER"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    private function loadMongoContainer()
    {
        $this->loadMockedContainer();
        $this->loadMockedMongoRepository();
    }

    public function tearDown()
    {
        $this->container = null;
        $this->repository = null;
        $this->service = null;
    }

    private function loadMockedContainer()
    {
        $this->container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
    }

    private function loadMockedMongoRepository()
    {
        $this->service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $this->repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $this->container->expects($this->once())
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($this->service));
        $this->service->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:User'))
            ->will($this->returnValue($this->repository));

    }

    public function testFormsaveWithoutParameters()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->formsaveAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    public function testFormsaveWithWrongParameters()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'nonexistant'
        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->formsaveAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    public function testFormsaveUpdateUser()
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer();
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId()

        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue($user));

        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')->disableOriginalConstructor()->getMock();

        $this->service->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($dm));

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->formsaveAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertFalse($json->error);
        $this->assertEquals($user->getId(), $json->newId);
    }

    public function testFormsaveUpdateUserDoesNotExist()
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer();
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId()

        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue(null));

        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')->disableOriginalConstructor()->getMock();

        $this->service->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($dm));

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->formsaveAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

}
