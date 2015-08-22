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
        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($service));
        $service->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:User'))
            ->will($this->returnValue($repository));
        $repository->expects($this->once())
            ->method("findAll")
            ->will($this->returnValue(array()));


        $controller = new DefaultController();
        $controller->setContainer($container);

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
        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($service));
        $service->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:User'))
            ->will($this->returnValue($repository));

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
        $repository->expects($this->once())
            ->method("findAll")
            ->will($this->returnValue($userArray));


        $controller = new DefaultController();
        $controller->setContainer($container);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->usersAction();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"id1","email":"email1","roles":"ROLE_ADMIN"},{"id":"id2","email":"email2","roles":"ROLE_USER"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

}
