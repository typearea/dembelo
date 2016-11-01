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

namespace AdminBundle\Tests\Controller;

use AdminBundle\Controller\TopicController;
use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TopicControllerTest
 */
class TopicControllerTest extends WebTestCase
{
    /**
     * tests topicAction() with no topics
     */
    public function testListActionWithNoTopics()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $parameterBag = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();

        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->query = $parameterBag;

        $controller = new TopicController($container, $repository);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->listAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests topicAction with one topic
     */
    public function testListActionWithOneTopic()
    {
        $topic = new Topic();
        $topic->setName('someName');
        $topic->setId('someId');
        $topic->setStatus(1);
        $topic->setSortKey(123);
        $topic->setOriginalImageName("someImageName");

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();
        $repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$topic]);

        $parameterBag = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();

        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->query = $parameterBag;

        $controller = new TopicController($container, $repository);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->listAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName","status":"1","sortKey":123,"originalImageName":"someImageName"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests topicAction with some filters
     */
    public function testListActionWithSomeFilters()
    {
        $topic = new Topic();
        $topic->setName('someName');
        $topic->setId('someId');
        $topic->setStatus(1);
        $topic->setSortKey(123);
        $topic->setOriginalImageName("someImageName");

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy', 'findFiltered'])->getMock();
        $repository->expects($this->never())
            ->method('findBy')
            ->willReturn([$topic]);
        $repository->expects($this->once())
            ->method('findFiltered')
            ->with(['status' => '1'])
            ->willReturn([$topic]);

        $parameterBag = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $parameterBag->expects($this->once())
            ->method('get')
            ->with('filter')
            ->willReturn(['status' => '1']);

        $request = $this->getMockBuilder(Request::class)->getMock();
        $request->query = $parameterBag;

        $controller = new TopicController($container, $repository);

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->listAction($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('[{"id":"someId","name":"someName","status":"1","sortKey":123,"originalImageName":"someImageName"}]', $response->getContent());
        $this->assertEquals('200', $response->getStatusCode());
    }

    /**
     * tests uploadImageAction with an error in $_FILES
     */
    public function testUploadImageActionWithNoFileError()
    {
        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->never())
            ->method('getParameter')
            ->with('topic_image_directory');

        $controller = new TopicController($container, $repository);

        $_FILES['upload'] = [
            'error' => UPLOAD_ERR_NO_FILE,
        ];

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->uploadImageAction();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString('{"status":"error"}', $response->getContent());
    }

    /**
     * tests uploadImageAction
     */
    public function testUploadImageActionWithNoError()
    {
        $repository = $this->getMockBuilder(TopicRepository::class)->disableOriginalConstructor()->setMethods(['findBy'])->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('getParameter')
            ->with('topic_image_directory')
            ->willReturn('someDirectory');

        $controller = new TopicController($container, $repository);

        $_FILES['upload'] = [
            'error' => UPLOAD_ERR_OK,
            'name' => 'someName',
            'tmp_name' => 'someTmpName',
        ];

        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $controller->uploadImageAction();
        $this->assertInstanceOf(Response::class, $response);
        $decoded = json_decode($response->getContent());
        $this->assertEquals('someName', $decoded->originalImageName);
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
}
