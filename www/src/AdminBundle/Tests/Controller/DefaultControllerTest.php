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
use DembeloMain\Model\Repository\Doctrine\ODM\ImportfileRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\LicenseeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\UserRepository;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AdminBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
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
     * @var \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    private $templatingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserRepository
     */
    private $userRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LicenseeRepository
     */
    private $licenseeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TopicRepository
     */
    private $topicRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private $importfileRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepository
     */
    private $textnodeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserPasswordEncoder
     */
    private $userPasswordEncoderMock;

    /**
     * @var string
     */
    private $topicImageDirectory;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->twineDirectory = '/tmp/twineDirectory';
        $this->templatingMock = $this->createTemplatingMock();
        $this->userRepositoryMock = $this->createUserRepositoryMock();
        $this->licenseeRepositoryMock = $this->createLicenseeRepositoryMock();
        $this->topicRepositoryMock = $this->createTopicRepositoryMock();
        $this->importfileRepositoryMock = $this->createImportfileRepositoryMock();
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->userPasswordEncoderMock = $this->createUserPasswordEncoderMock();
        $this->topicImageDirectory = '/tmp/topicImageDirectory';

        $this->controller = new DefaultController(
            $this->templatingMock,
            $this->userRepositoryMock,
            $this->licenseeRepositoryMock,
            $this->topicRepositoryMock,
            $this->importfileRepositoryMock,
            $this->userPasswordEncoderMock,
            $this->twineDirectory,
            $this->topicImageDirectory
        );
    }

    /**
     * tear down method
     * @return void
     */
    public function tearDown(): void
    {
        $this->service = null;
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

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue($user));

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

        $this->userRepositoryMock->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->will($this->returnValue(null));

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

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->formsaveAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
        $this->assertTrue($json->error);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LicenseeRepository
     */
    private function createLicenseeRepositoryMock(): LicenseeRepository
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
    private function createImportfileRepositoryMock(): ImportfileRepository
    {
        $repository = $this->getMockBuilder(ImportfileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findAll'])
            ->getMock();

        return $repository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    private function createTemplatingMock(): EngineInterface
    {
        return $this->createMock(EngineInterface::class);
    }

    /**
     * @return UserRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserRepositoryMock(): UserRepository
    {
        return $this->createMock(UserRepository::class);
    }

    /**
     * @return TopicRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTopicRepositoryMock(): TopicRepository
    {
        return $this->createMock(TopicRepository::class);
    }

    /**
     * @return TextNodeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTextnodeRepositoryMock(): TextNodeRepository
    {
        return $this->createMock(TextNodeRepository::class);
    }

    /**
     * @return UserPasswordEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserPasswordEncoderMock(): UserPasswordEncoder
    {
        return $this->createMock(UserPasswordEncoder::class);
    }
}
