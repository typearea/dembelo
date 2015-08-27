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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Controller\DefaultController;
use DembeloMain\Document\Story;
use DembeloMain\Document\Textnode;

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

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Dembelo")')->count() > 0);
    }

    /**
     * @brief Tests the action of reading a textnode without an active
     *     user session.
     */
    public function testReadWithoutLogin()
    {
        $container = $this->getMock("Symfony\Component\DependencyInjection\ContainerInterface");
        $securityContext = $this->getMockBuilder("Symfony\Component\Security\Core\SecurityContext")->disableOriginalConstructor()->getMock();
        $service = $this->getMockBuilder("Doctrine\Bundle\MongoDBBundle\ManagerRegistry")->disableOriginalConstructor()->getMock();
        $connection = $this->getMockBuilder("Doctrine\MongoDB\Connection")->disableOriginalConstructor()->getMock();
        $repository = $this->getMockBuilder("Doctrine\ODM\MongoDB\DocumentRepository")->disableOriginalConstructor()->getMock();
        $template = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $response = $this->getMock("Symfony\Component\HttpFoundation\Response");

        $container->expects($this->at(0))
            ->method("get")
            ->with($this->equalTo('security.context'))
            // Security context without any privileges.
            ->will($this->returnValue($securityContext));
        $container->expects($this->at(1))
            ->method("get")
            ->with($this->equalTo('doctrine_mongodb'))
            ->will($this->returnValue($service));
        $service->expects($this->at(0))
            ->method("getConnection")
            ->will($this->returnValue($connection));
        $service->expects($this->at(1))
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Story'))
            ->will($this->returnValue($repository));
        $service->expects($this->at(2))
            ->method("getRepository")
            ->with($this->equalTo('DembeloMain:Textnode'))
            ->will($this->returnValue($repository));

        $story = new Story();
        $story->setId("55d2b934658f5cc23c3c986d");
        $story->setName("Lorem I");
        $story->setTopicId("55d2b934658f5cc23c3c986c");
        $story->setStatus(Story::STATUS_ACTIVE);

        $repository->expects($this->once())
            ->method("findOneBy")
            ->will($this->returnValue($story));

        $textnode = new Textnode();
        $textnode->setTopicId("55d2b934658f5cc23c3c986c");
        $textnode->setStoryId("55d2b934658f5cc23c3c986d");
        $textnode->setType(Textnode::TYPE_INTRODUCTION);
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setId(1);
        $textnode->setText("Lorem ipsum dolor sit amet.");

        $repository->expects($this->once())
            ->method("findBy")
            ->will($this->returnValue(array($textnode)));

        $container->expects($this->at(2))
            ->method("get")
            ->with("templating")
            ->will($this->returnValue($template));
        $template->expects($this->once())
            ->method("renderResponse")
            ->with("default/read.html.twig", array("textnodeText" => "Lorem ipsum dolor sit amet."))
            ->will($this->returnValue($response));

        $controller = new DefaultController();
        $controller->setContainer($container);

        $result = $controller->readAction("55d2b934658f5cc23c3c986c");
    }

    /** @todo Implement testReadWithLogin(). */
}
