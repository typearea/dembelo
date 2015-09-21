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
 *
 * @package DembeloMain
 */
namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Topic;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Textnode
 *
 * @MongoDB\Document
 */
class Textnode
{

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
     * @MongoDb\ObjectId
     */
    protected $topicId;

    /**
     * @MongoDB\Int
     */
    protected $status;

    /**
     * @MongoDB\String
     */
    protected $text;

    /**
     * @MongoDB\Bool
     */
    protected $access;

    /**
     * @MongoDB\Hash
     */
    protected $metadata;

    /**
     * @MongoDB\ObjectId
     */
    protected $licenseeId;

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
     * @return String
     */
    public function getTopicId()
    {
        return $this->topicId;
    }

    /**
     * @param String $topicId
     */
    public function setTopicId($topicId)
    {
        $this->topicId = $topicId;
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

    /**
     * gets the textnode's metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * sets the textnode's metadata
     *
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * sets the textnode's licensee ID
     *
     * @param String $licenseeId
     */
    public function setLicenseeId($licenseeId)
    {
        $this->licenseeId = $licenseeId;
    }

    /**
     * gets the textnode's licensee ID
     *
     * @return String
     */
    public function getLicenseeId()
    {
        return $this->licenseeId;
    }

    /**
     * sets the access parameter
     *
     * @param bool $access
     */
    public function setAccess($access)
    {
        $this->access = (bool) $access;
    }

    /**
     * gets the access parameter
     *
     * @return bool
     */
    public function getAccess()
    {
        return $this->access;
    }
}
