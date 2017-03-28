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

namespace DembeloMain\Test\Model;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use DembeloMain\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Model\FavoriteManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class FavoriteManagerTest
 * @package DembeloMain\Test\Model
 */
class FavoriteManagerTest extends WebTestCase
{
    /* @var FavoriteManager */
    private $favMgr;
    private $topic;
    private $textnode;
    /* @var Session */
    private $session;
    private $user;
    private $textnodeRepository;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->topic = new Topic();
        $this->topic->setId('topicId');
        $this->textnode = new Textnode();
        $this->textnode->setArbitraryId('textnodeId');
        $this->textnode->setTopicId('topicId');
        $this->user = new User();

        $this->session = new Session(new MockArraySessionStorage());

        $this->favMgr = new FavoriteManager($this->session);
    }

    /**
     * tests setting of a favorite via cookie
     */
    public function testSetFavoriteWithCookie()
    {
        $this->favMgr->setFavorite($this->textnode);
        $this->assertEquals('textnodeId', $this->favMgr->getFavorite($this->topic));
        $this->assertEquals('textnodeId', $this->session->get('favorite_topicId'));
    }

    /**
     * tests setting of a favorite via user object
     */
    public function testSetFavoriteWithUser()
    {
        $this->favMgr->setFavorite($this->textnode, $this->user);
        $this->assertEquals('textnodeId', $this->favMgr->getFavorite($this->topic, $this->user));
        $this->assertNull($this->session->get('favorite_topicId'));
    }

    /**
     * tests setting of a favorite across multiple instances via user object
     */
    public function testSetFavoriteWithMultipleInstancesWithUser()
    {
        $this->favMgr->setFavorite($this->textnode, $this->user);

        $favMgr = new FavoriteManager($this->session, $this->textnodeRepository);
        $this->assertEquals('textnodeId', $favMgr->getFavorite($this->topic, $this->user));
        $this->assertNull($this->session->get('favorite_topicId'));
    }

    /**
     * tests setting of a favorite across multiple instances via cookie
     */
    public function testSetFavoriteWithMultipleInstancesWithCookie()
    {
        $this->favMgr->setFavorite($this->textnode);

        $favMgr = new FavoriteManager($this->session, $this->textnodeRepository);
        $this->assertEquals('textnodeId', $favMgr->getFavorite($this->topic));
        $this->assertEquals('textnodeId', $this->session->get('favorite_topicId'));
    }
}