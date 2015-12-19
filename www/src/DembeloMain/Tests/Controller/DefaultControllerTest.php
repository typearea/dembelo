<?php

/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
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
 * @package DembeloMain
 */

namespace DembeloMain\Tests\Controller;

use DembeloMain\Document\Topic;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Controller\DefaultController;
use DembeloMain\Document\Textnode;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * Tests the index action.
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Dembelo")')->count() > 0);
    }

    /**
     * @todo Add a more extensive indexAction test, which checks the logic
     *     for retrieving the topics if there isn't an active user session,
     *     and the redirecting if a current textnode was saved.
     */

    /**
     * Tests how the first textnode of a topic gets found based on the topic ID.
     */
    public function testReadTopic()
    {
        $textnode = new Textnode();
        $textnode->setId("55f5ab3708985c4b188b4577");
        $textnode->setTopicId("foobar");
        $textnode->setAccess(true);
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setText("Lorem ipsum dolor sit amet.");

        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $mongo = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $router = $this->getMock("Symfony\Component\Routing\RouterInterface");
        $queryBuilder = $this->getMockBuilder("Doctrine\ODM\MongoDB\QueryBuilder")->setMethods(array('field', 'equals', 'getQuery', 'getSingleResult'))->getMock();

        $container->expects($this->at(0))
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($mongo));
        $mongo->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Textnode'))
            ->will($this->returnValue($repository));

        $queryBuilder->expects($this->at(0))
            ->method('field')
            ->with('topicId')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->at(1))
            ->method('equals')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->at(2))
            ->method('field')
            ->with('status')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->at(3))
            ->method('equals')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->at(4))
            ->method('field')
            ->with('access')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->at(5))
            ->method('equals')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('getSingleResult')
            ->will($this->returnValue($textnode));

        $repository->expects($this->once())
            ->method("createQueryBuilder")
            ->will($this->returnValue($queryBuilder));

        $container->expects($this->at(1))
            ->method("get")
            ->with($this->equalTo('router'))
            ->will($this->returnValue($router));
        $router->expects($this->once())
            ->method("generate")
            ->with("text", array('textnodeId' => $textnode->getId()))
            ->will($this->returnValue("text/".$textnode->getId()));

        $controller = new DefaultController();
        $controller->setContainer($container);

        $result = $controller->readTopicAction("55d2b934658f5cc23c3c986c");

        $this->assertEquals('Symfony\Component\HttpFoundation\RedirectResponse', get_class($result));
        $this->assertEquals('302', $result->getStatusCode());
        $this->assertEquals('text/'.$textnode->getId(), $result->getTargetUrl());
    }

    /**
     * Tests the action of reading a textnode without an active
     *     user session.
     */
    public function testReadTextnodeWithoutLogin()
    {
        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $mongo = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $authorizationChecker = $this->getMockBuilder('foobar')->setMethods(array('isGranted'))->getMock();
        $tokenStorage = $this->getMockBuilder("Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage")->disableOriginalConstructor()->getMock();
        $template = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');

        $container->expects($this->at(0))
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($mongo));
        $mongo->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Textnode'))
            ->will($this->returnValue($repository));

        $hitch = array();
        $hitch['textnodeId'] = "55f5ab3708985c4b188b4578";
        $hitch['description'] = "Continue.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        $textnodeId = "55f5ab3708985c4b188b4577";

        $textnode = new Textnode();
        $textnode->setId($textnodeId);
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setText("Lorem ipsum dolor sit amet.");
        $textnode->appendHitch($hitch);

        $repository->expects($this->once())
            ->method("findBy")
            ->with(array('id' => new \MongoId($textnodeId), 'status' => Textnode::STATUS_ACTIVE))
            ->will($this->returnValue(array($textnode)));

        $container->expects($this->at(1))
            ->method("get")
            ->with($this->equalTo('security.authorization_checker'))
            ->will($this->returnValue($authorizationChecker));
        $container->expects($this->at(2))
            ->method("get")
            ->with($this->equalTo('security.token_storage'))
            ->will($this->returnValue($tokenStorage));

        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('ROLE_USER'))
            ->will($this->returnValue(false));

        $container->expects($this->at(3))
            ->method("get")
            ->with("templating")
            ->will($this->returnValue($template));
        $template->expects($this->once())
            ->method("renderResponse")
            ->with("default/read.html.twig", array("textnode" => $textnode, 'hyphenated' => 'Lo&shy;rem ip&shy;sum do&shy;lor sit amet.'))
            ->will($this->returnValue('renderresponse'));

        $controller = new DefaultController();
        $controller->setContainer($container);

        $result = $controller->readTextnodeAction($textnodeId);

        $this->assertEquals('renderresponse', $result);
    }

    /** @todo Implement testReadTextnodeWithLogin(). */
    public function xtestPaywallAction()
    {
        $textnodeId = 'textnode1';
        $hitchIndex = 123;
        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($service));
        $service->expects($this->once())
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Textnode'))
            ->will($this->returnValue($repository));

        $controller = new DefaultController();
        $controller->setContainer($container);

        $response = $controller->paywallAction($textnodeId, $hitchIndex);
        $json = $response->getContent();
        $this->assertJson($json);
        $json = json_decode($json);
    }
}
