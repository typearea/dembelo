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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;

/**
 * Class Textnode
 *
 * @MongoDB\Document
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository")
 */
class Textnode
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $created;

    /**
     * @MongoDb\Field(type="object_id")
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
     * @MongoDB\Field(type="hash")
     */
    protected $metadata;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $licenseeId;

    /**
     * @var TextnodeHitch[]|Collection
     * @ReferenceMany(targetDocument="TextnodeHitch", mappedBy="targetTextnode")
     */
    protected $parentHitches;

    /**
     * @var TextnodeHitch[]|Collection
     * @ReferenceMany(targetDocument="TextnodeHitch", mappedBy="sourceTextnode")
     */
    protected $childHitches;

    /**
     * @MongoDB\Field(type="object_id")
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

    public function __construct()
    {
        $this->childHitches = new ArrayCollection();
        $this->parentHitches = new ArrayCollection();
    }

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
     * @return String|null
     */
    public function getTopicId(): ?string
    {
        return $this->topicId;
    }

    /**
     * @param String $topicId
     *
     * @return void
     */
    public function setTopicId($topicId): void
    {
        $this->topicId = $topicId;
    }

    /**
     * @return integer
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param integer $status
     *
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * gets the mongodb id
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * sets the mongoDB id
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
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
     *
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * gets the textnode's hyphenated text
     *
     * @return string
     */
    public function getTextHyphenated(): ?string
    {
        return $this->textHyphenated;
    }

    /**
     * sets the textnode's hyphenated text
     *
     * @param string $textHyphenated
     */
    public function setTextHyphenated($textHyphenated): void
    {
        $this->textHyphenated = $textHyphenated;
    }

    /**
     * gets the textnode's metadata
     *
     * @return array
     */
    public function getMetadata(): ?array
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
     *                     from which a reading path begins.
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
     * @return TextnodeHitch[]|Collection
     */
    public function getChildHitches(): Collection
    {
        return $this->childHitches;
    }

    /**
     * @return TextnodeHitch[]|Collection
     */
    public function getParentHitches(): Collection
    {
        return $this->parentHitches;
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
    public function isFinanceNode(): bool
    {
        return $this->getChildHitches()->isEmpty();
    }
}
