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
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository")
 */
class Textnode
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const HITCH_STATUS_INACTIVE = 0;
    const HITCH_STATUS_ACTIVE = 1;

    const HITCHES_MAXIMUM_COUNT = 8;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $created;

    /**
     * @MongoDb\ObjectId
     */
    protected $topicId;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $status;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $text;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $textHyphenated;

    /**
     * @MongoDB\Field(type="boolean")
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
     * @MongoDB\Hash
     */
    protected $hitches = [];

    /**
     * @MongoDB\ObjectId
     */
    protected $importfileId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $twineId;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $arbitraryId;

    /**
     * gets the timestamp of creation
     *
     * @return \DateTime
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
     * gets the textnode's hyphenated text
     *
     * @return string
     */
    public function getTextHyphenated()
    {
        return $this->textHyphenated;
    }

    /**
     * sets the textnode's hyphenated text
     *
     * @param string $textHyphenated
     */
    public function setTextHyphenated($textHyphenated)
    {
        $this->textHyphenated = $textHyphenated;
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
     * @param bool $access true if this Textnode is a start textnode
     *     from which a reading path begins.
     */
    public function setAccess($access)
    {
        $this->access = (bool) $access;
    }

    /**
     * gets the access parameter
     *
     * @return bool true|false true, if this Textnode is a start textnode
     *     from which a reading path begins.
     */
    public function getAccess()
    {
        return $this->access;
    }


    /**
     * Counts the number of hitches within the collection.
     *
     * @return Number of hitches within the collection.
     */
    public function getHitchCount()
    {
        return count($this->hitches);
    }

    /**
     * Gets the hitch which is stored in the collection at the
     *     specified index.
     *
     * @param int $hitchIndex
     *
     * @return array|null Null, if the $hitchIndex doesn't specify
     *     an element within the collection.
     */
    public function getHitch($hitchIndex)
    {
        if ($hitchIndex < 0 || $hitchIndex >= count($this->hitches)) {
            return null;
        }

        return $this->hitches[$hitchIndex];
    }

    /**
     * Appends a hitch at the end of the collection.
     *
     * @param array $hitch
     *
     * @return true|false False, if $hitch doesn't contain all
     *     associative array elements which form a hitch, or if
     *     no "textnodeId" is set within $hitch, or if "status"
     *     within $hitch doesn't contain a valid value, or if
     *     there are already HITCHES_MAXIMUM_COUNT hitches present.
     */
    public function appendHitch(array $hitch)
    {
        $hitchCount = count($this->hitches);

        if ($hitchCount >= Textnode::HITCHES_MAXIMUM_COUNT) {
            return false;
        }

        if (array_key_exists("textnodeId", $hitch) !== true) {
            return false;
        }

        if (array_key_exists("description", $hitch) !== true) {
            return false;
        }

        if (array_key_exists("status", $hitch) !== true) {
            return false;
        }

        if ($hitch['status'] !== Textnode::HITCH_STATUS_INACTIVE &&
            $hitch['status'] !== Textnode::HITCH_STATUS_ACTIVE) {
            return false;
        }

        if (is_null($hitch['textnodeId']) === true) {
            return false;
        }

        $this->hitches[$hitchCount] = $hitch;

        return true;
    }

    /**
     * Overwrites the hitch which is stored in the collection at the
     *     specified index.
     *
     * @param int   $hitchIndex
     * @param array $hitch
     *
     * @return true|false False, if the $hitchIndex doesn't specify
     *     an element within the collection, or if $hitch doesn't
     *     contain all associative array elements which form a hitch,
     *     or if no "textnodeId" is set within $hitch, or if "status"
     *     within $hitch doesn't contain a valid value.
     */
    public function setHitch($hitchIndex, array $hitch)
    {
        if ($hitchIndex < 0 || $hitchIndex >= count($this->hitches)) {
            return false;
        }

        if (array_key_exists("textnodeId", $hitch) !== true) {
            return false;
        }

        if (array_key_exists("description", $hitch) !== true) {
            return false;
        }

        if (array_key_exists("status", $hitch) !== true) {
            return false;
        }

        if ($hitch['status'] !== Textnode::HITCH_STATUS_INACTIVE &&
            $hitch['status'] !== Textnode::HITCH_STATUS_ACTIVE) {
            return false;
        }

        if (is_null($hitch['textnodeId']) === true) {
            return false;
        }

        $this->hitches[$hitchIndex] = $hitch;

        return true;
    }

    /**
     * Removes the hitch which is stored in the collection at the
     *     specified index. Reordering of the hitch collection will
     *     take place, former collection indexes might become invalid
     *     by calling removeHitch().
     *
     * @param int $hitchIndex
     *
     * @return true|false False, if the collection is already empty or
     *     the $hitchIndex doesn't specify an element within the collection.
     */
    public function removeHitch($hitchIndex)
    {
        $hitchCount = count($this->hitches);

        if ($hitchCount <= 0) {
            return false;
        }

        if ($hitchIndex < 0 || $hitchIndex >= $hitchCount) {
            return false;
        }

        array_splice($this->hitches, $hitchIndex, 1);

        return true;
    }

    /**
     * sets the importfile ID
     *
     * @param string $importfileId
     */
    public function setImportfileId($importfileId)
    {
        $this->importfileId = $importfileId;
    }

    /**
     * returns importfile ID
     *
     * @return string
     */
    public function getImportfileId()
    {
        return $this->importfileId;
    }

    /**
     * sets the twine ID
     *
     * @param string $twineId
     */
    public function setTwineId($twineId)
    {
        $this->twineId = $twineId;
    }

    /**
     * returns twine ID
     *
     * @return string
     */
    public function getTwineId()
    {
        return $this->twineId;
    }

    /**
     * sets the arbitrary ID
     *
     * @param string $arbitraryId
     */
    public function setArbitraryId($arbitraryId)
    {
        $this->arbitraryId = $arbitraryId;
    }

    /**
     * returns arbitrary ID
     *
     * @return string
     */
    public function getArbitraryId()
    {
        return $this->arbitraryId;
    }

    /**
     * returns true if textnode is a finance node
     *
     * @return bool
     */
    public function isFinanceNode()
    {
        return $this->getHitchCount() === 0;
    }

    /**
     * removes all hitches
     */
    public function clearHitches()
    {
        $this->hitches = [];
    }
}
