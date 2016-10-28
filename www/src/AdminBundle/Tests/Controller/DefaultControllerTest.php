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

use DembeloMain\Document\Licensee;
use DembeloMain\Document\Topic;
use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AdminBundle\Controller\DefaultController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
    public function testIndexAction()
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
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $queryMock = $this->getMockBuilder('foobar')->setMethods(array('execute', 'getQuery'))->getMock();
        $postArray = array();
        $postMock->expects($this->once())
            ->method("get")
            ->will($this->returnValue($postArray));
        $request->query = $postMock;

        $queryMock->expects($this->once())
            ->method("getQuery")
            ->will($this->returnSelf());

        $queryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue(array()));

        $this->loadMongoContainer('user');
        $this->repository->expects($this->once())
            ->method("createQueryBuilder")
            ->will($this->returnValue($queryMock));

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->usersAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests controller's userAction with two users in db
     */
    public function testUserActionWithUsers()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $queryMock = $this->getMockBuilder('foobar')->setMethods(['execute', 'getQuery'])->getMock();
        $postArray = array();
        $postMock->expects($this->once())
            ->method("get")
            ->will($this->returnValue($postArray));
        $request->query = $postMock;

        $queryMock->expects($this->once())
            ->method("getQuery")
            ->will($this->returnSelf());

        $this->loadMongoContainer('user');

        $this->repository->expects($this->once())
            ->method("createQueryBuilder")
            ->will($this->returnValue($queryMock));

        $user1 = new User();
        $user1->setEmail('email1');
        $user1->setId('id1');
        $user1->setRoles('ROLE_ADMIN');
        $user1->setLicenseeId('lic1');
        $user2 = new User();
        $user2->setEmail('email2');
        $user2->setId('id2');
        $user2->setRoles('ROLE_USER');
        $user2->setLicenseeId('lic2');

        $userArray = array(
            $user1,
            $user2,
        );

        $queryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($userArray));

        $controller = new DefaultController();
        $controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->usersAction($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"id1","gender":null,"email":"email1","roles":"ROLE_ADMIN","licenseeId":"lic1","status":null,"source":null,"reason":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"},{"id":"id2","email":"email2","roles":"ROLE_USER","licenseeId":"lic2","status":null,"source":null,"reason":null,"gender":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tear down method
     */
    public function tearDown()
    {
        $this->container = null;
        $this->repository = null;
        $this->service = null;
    }

    /**
     * tests the formsaveAction without parameters
     */
    public function testFormsaveActionWithoutParameters()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array();
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

    /**
     * tests the formsaveAction with wrong parameters
     */
    public function testFormsaveActionWithWrongParameters()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'nonexistant',
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

    /**
     * tests the formsaveAction with an existing user
     */
    public function testFormsaveActionExistingUser()
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer('user');
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId(),
        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue($user));

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

    /**
     * tests the formsaveAction with a nonexisting user
     */
    public function testFormsaveActionNotExistingUser()
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer('user');
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder("Symfony\Component\HttpFoundation\ParameterBag")->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId(),
        );
        $postMock->expects($this->once())
            ->method("all")
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue(null));

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

    /**
     * tests the formsaveAction without admin permission
     */
    public function testFormsaveActionWithoutAdminPermission()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/save');


        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("login")')->count() > 0);
    }

    /**
     * tests topicAction() with no topics
     */
    public function testTopicActionWithNoTopics()
    {
        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('app.model_repository_topic')
            ->willReturn($repository);

        $controller = new DefaultController();
        $controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->topicsAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests topicAction with one topic
     */
    public function testTopicActionWithOneTopic()
    {
        $topic = new Topic();
        $topic->setName('someName');
        $topic->setId('someId');
        $topic->setStatus(1);
        $topic->setSortKey(123);

        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$topic]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('app.model_repository_topic')
            ->willReturn($repository);

        $controller = new DefaultController();
        $controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->topicsAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName","status":"1","sortKey":123}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests licenseeAction with no licensees
     */
    public function testLicenseeActionWithNoLicensees()
    {
        $repository = $this->getMockBuilder(LicenseeRepository::class)->disableOriginalConstructor()->setMethods(['findAll'])->getMock();
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('app.model_repository_licensee')
            ->willReturn($repository);

        $controller = new DefaultController();
        $controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->licenseesAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests licenseeAction() with one licensee
     */
    public function testLicenseeActionWithOneLicensee()
    {
        $licensee = new Licensee();
        $licensee->setName('someName');
        $licensee->setId('someId');

        $repository = $this->getMockBuilder(LicenseeRepository::class)->disableOriginalConstructor()->setMethods(['findAll'])->getMock();
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$licensee]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('app.model_repository_licensee')
            ->willReturn($repository);

        $controller = new DefaultController();
        $controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->licenseesAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * load mockoed container and mocked mongodb repository
     */
    private function loadMongoContainer($repository)
    {
        $this->loadMockedContainer();
        $this->loadMockedMongoRepository($repository);
    }

    /**
     * load mocked container
     */
    private function loadMockedContainer()
    {
        $this->container = $this->createMock("Symfony\Component\DependencyInjection\ContainerInterface");
    }

    /**
     * load mocked mongodb repository
     */
    private function loadMockedMongoRepository($repository)
    {
        $this->service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $this->repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $this->container->expects($this->once())
            ->method("get")
            ->with($this->equalTo('app.model_repository_'.$repository))
            ->will($this->returnValue($this->repository));
    }
}
