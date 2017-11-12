<?php
/* Copyright (C) 2017 Michael Giesler
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

namespace AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
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

        $this->userRepositoryMock->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryMock));

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

        $this->userRepositoryMock->expects($this->once())
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

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $this->controller->usersAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"id1","gender":null,"email":"email1","roles":"ROLE_ADMIN","licenseeId":"lic1","status":null,"source":null,"reason":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"},{"id":"id2","email":"email2","roles":"ROLE_USER","licenseeId":"lic2","status":null,"source":null,"reason":null,"gender":null,"created":"'.date('Y-m-d H:i:s', 0).'","updated":"'.date('Y-m-d H:i:s', 0).'"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }
}