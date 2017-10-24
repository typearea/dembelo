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

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\ImportfileRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\LicenseeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AdminBundle\Controller\DefaultController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DocumentRepository
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $service;

    /**
     * @var DefaultController
     */
    private $controller;

    /**
     * @var string
     */
    private $twineDirectory;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->twineDirectory = '/tmp/twineDirectory';
        $this->controller = new DefaultController($this->twineDirectory);
    }

    /**
     * tests the index action
     * @return void
     */
    public function testIndexAction(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("login")')->count() > 0);
    }

    /**
     * tests controller's userAction with no users in db
     * @return void
     */
    public function testUserAction(): void
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $queryMock = $this->getMockBuilder('foobar')->setMethods(array('execute', 'getQuery'))->getMock();
        $postArray = array();
        $postMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($postArray));
        $request->query = $postMock;

        $queryMock->expects($this->once())
            ->method('getQuery')
            ->will($this->returnSelf());

        $queryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(array()));

        $this->loadMongoContainer('user');
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryMock));

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->usersAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests controller's userAction with two users in db
     * @return void
     */
    public function testUserActionWithUsers(): void
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $queryMock = $this->getMockBuilder('foobar')->setMethods(['execute', 'getQuery'])->getMock();
        $postArray = array();
        $postMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($postArray));
        $request->query = $postMock;

        $queryMock->expects($this->once())
            ->method('getQuery')
            ->will($this->returnSelf());

        $this->loadMongoContainer('user');

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
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
            ->method('execute')
            ->will($this->returnValue($userArray));

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->usersAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"id1","gender":null,"email":"email1","roles":"ROLE_ADMIN","licenseeId":"lic1","status":null,"source":null,"reason":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"},{"id":"id2","email":"email2","roles":"ROLE_USER","licenseeId":"lic2","status":null,"source":null,"reason":null,"gender":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tear down method
     * @return void
     */
    public function tearDown(): void
    {
        $this->container = null;
        $this->repository = null;
        $this->service = null;
    }

    /**
     * tests the formsaveAction without parameters
     * @return void
     */
    public function testFormsaveActionWithoutParameters(): void
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $postArray = array();
        $postMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    /**
     * tests the formsaveAction with wrong parameters
     * @return void
     */
    public function testFormsaveActionWithWrongParameters(): void
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'nonexistant',
        );
        $postMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    /**
     * tests the formsaveAction with an existing user
     * @return void
     */
    public function testFormsaveActionExistingUser(): void
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer('user');
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId(),
        );
        $postMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue($user));

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertFalse($json->error);
        $this->assertEquals($user->getId(), $json->newId);
    }

    /**
     * tests the formsaveAction with a nonexisting user
     * @return void
     */
    public function testFormsaveActionNotExistingUser(): void
    {
        $user = new User();
        $user->setId('someId');
        $user->setEmail('some@email.de');
        $user->setRoles('ROLE_USER');

        $this->loadMongoContainer('user');
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'user',
            'id' => $user->getId(),
        );
        $postMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->repository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue(null));

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    /**
     * tests the formsaveAction without admin permission
     * @return void
     */
    public function testFormsaveActionWithoutAdminPermission(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/save');


        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("login")')->count() > 0);
    }

    /**
     * tests the formsaveAction with missing id parameter
     * @return void
     */
    public function testFormsaveActionWithMissingIdParameter(): void
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $postMock = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();
        $postArray = array(
            'formtype' => 'licensee',
            'name' => 'someLNName',
        );
        $postMock->expects($this->once())
            ->method('all')
            ->will($this->returnValue($postArray));
        $request->request = $postMock;

        $this->controller->setContainer($this->container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    /**
     * tests licenseeAction with no licensees
     * @return void
     */
    public function testLicenseeActionWithNoLicensees(): void
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

        $this->controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->licenseesAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests licenseeAction() with one licensee
     * @return void
     */
    public function testLicenseeActionWithOneLicensee(): void
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

        $this->controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->licenseesAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests textnode action
     * @return void
     */
    public function testTextnodesAction(): void
    {
        $textnode = new Textnode();
        $textnode->setId('someId');
        $textnode->setCreated(new \DateTime('2017-01-01 12:00:00'));
        $textnode->setStatus(1);
        $textnode->setAccess(true);
        $textnode->setLicenseeId('someLicenseeId');
        $textnode->setArbitraryId('someArbitraryId');
        $textnode->setTwineId('someTwineId');
        $textnode->setMetadata(['key1' => 'val1', 'key2' => 'val2']);

        $licensee = new Licensee();
        $licensee->setId('someLicenseeId');
        $licensee->setName('someLicenseeName');

        $importfile = new Importfile();
        $importfile->setId('someImportfileId');
        $importfile->setName('someImportfileName');

        $repository = $this->getMockBuilder(TextNodeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll'])
            ->getMock();

        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$textnode]);

        $licenseeRepository = $this->getLicenseeRepositoryMock();
        $licenseeRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$licensee]);

        $importfileRepository = $this->getImportfileRepositoryMock();
        $importfileRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$importfile]);


        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->at(0))
            ->method('get')
            ->with('app.model_repository_textNode')
            ->willReturn($repository);
        $container->expects($this->at(1))
            ->method('get')
            ->with('app.model_repository_licensee')
            ->willReturn($licenseeRepository);
        $container->expects($this->at(2))
            ->method('get')
            ->with('app.model_repository_importfile')
            ->willReturn($importfileRepository);

        $this->controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->textnodesAction();
        $this->assertInstanceOf(Response::class, $response);
        $expectedJson = '[{"id":"someId","status":"aktiv","created":"01.01.2017, 12:00:00",';
        $expectedJson .= '"access":"ja","licensee":"someLicenseeName","importfile":"unbekannt","beginning":"...",';
        $expectedJson .= '"financenode":"ja","arbitraryId":"someArbitraryId","twineId":"someTwineId",';
        $expectedJson .= '"metadata":"key1: val1\nkey2: val2\n"}]';
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LicenseeRepository
     */
    private function getLicenseeRepositoryMock(): LicenseeRepository
    {
        $repository = $this->getMockBuilder(LicenseeRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll'])
            ->getMock();

        return $repository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImportfileRepository
     */
    private function getImportfileRepositoryMock(): ImportfileRepository
    {
        $repository = $this->getMockBuilder(ImportfileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll'])
            ->getMock();

        return $repository;
    }

    /**
     * load mockoed container and mocked mongodb repository
     * @return void
     */
    private function loadMongoContainer($repository): void
    {
        $this->loadMockedContainer();
        $this->loadMockedMongoRepository($repository);
    }

    /**
     * load mocked container
     * @return void
     */
    private function loadMockedContainer(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * load mocked mongodb repository
     * @return void
     */
    private function loadMockedMongoRepository($repository): void
    {
        $this->service = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $this->repository = $this->getMockBuilder(DocumentRepository::class)->disableOriginalConstructor()->getMock();
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('app.model_repository_'.$repository))
            ->will($this->returnValue($this->repository));
    }
}
