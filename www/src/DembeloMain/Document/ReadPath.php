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
 * Class ReadPath
 *
 * @MongoDB\Document
 */
class ReadPath
{

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ObjectId
     */
    protected $user_id;

    /**
     * @MongoDB\ObjectId
     */
    protected $textnode_id;

    /**
     * @MongoDB\Date
     */
    protected $timestamp;

    /**
     * @MongoDB\ObjectId
     */
    protected $previous_textnode_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getTextnodeId()
    {
        return $this->textnode_id;
    }

    /**
     * @param mixed $textnode_id
     */
    public function setTextnodeId($textnode_id)
    {
        $this->textnode_id = $textnode_id;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getPreviousTextnodeId()
    {
        return $this->previous_textnode_id;
    }

    /**
     * @param mixed $previous_textnode_id
     */
    public function setPreviousTextnodeId($previous_textnode_id)
    {
        $this->previous_textnode_id = $previous_textnode_id;
    }

}
