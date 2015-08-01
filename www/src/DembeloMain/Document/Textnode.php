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
class Textnode {

    const TYPE_INTRODUCTION = 0;
    const TYPE_DEEPENING = 1;

    const STATUS_ACTIVE = 0;
    const STATUS_INACTIVE = 1;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ObjectId
     */
    protected $author_id;

    /**
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @MongoDB\ObjectId
     */
    protected $topic_id;

    /**
     * @MongoDB\ObjectId
     */
    protected $story_id;

    /**
     * @MongoDB\Int
     */
    protected $type;

    /**
     * @MongoDB\Int
     */
    protected $status;

    /**
     * @return mixed
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * sets the author id
     *
     * @param string $author_id
     */
    public function setAuthorId($author_id)
    {
        $this->author_id = $author_id;
    }

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
        return $this->topic_id;
    }

    /**
     * @param string $topic_id
     */
    public function setTopicId($topic_id)
    {
        $this->topic_id = $topic_id;
    }

    /**
     * @return string
     */
    public function getStoryId()
    {
        return $this->story_id;
    }

    /**
     * @param string $story_id
     */
    public function setStoryId($story_id)
    {
        $this->story_id = $story_id;
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


}
