<?php

/* Copyright (C) 2015 Michael Giesler <michael@horsemen.de>
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
 *
 * @package DembeloMain
 */
namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Textnode
 *
 * @MongoDB\Document
 */
class Textnode
{
    const TYPE_INTRODUCTION = 0;
    const TYPE_DEEPENING = 1;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @MongoDB\ObjectId
     */
    protected $topicId;

    /**
     * @MongoDB\ObjectId
     */
    protected $storyId;

    /**
     * @MongoDB\Int
     */
    protected $type;

    /**
     * @MongoDB\Int
     */
    protected $status;

    /**
     * @MongoDB\String
     */
    protected $text;

    /**
     * gets the timestamp of creation
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * sets the timestamp of creation
     *
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getTopicId()
    {
        return $this->topicId;
    }

    /**
     * @param string $topicId
     */
    public function setTopicId($topicId)
    {
        $this->topicId = $topicId;
    }

    /**
     * @return string
     */
    public function getStoryId()
    {
        return $this->storyId;
    }

    /**
     * @param string $storyId
     */
    public function setStoryId($storyId)
    {
        $this->storyId = $storyId;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * gets the mongodb id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * sets the mongoDB id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * gets the textnode's text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * sets the textnode's text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}
