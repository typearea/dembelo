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


/**
 * @package DembeloMain
 */

namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class Importfile
 *
 * @MongoDB\Document
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\ImportfileRepository")
 */
class Importfile
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $licenseeId;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    protected $imported;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $author;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $publisher;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $originalname;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $filename;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $topicId;

    /**
     * gets the mongodb id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * sets the mongoDB id
     *
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * gets the name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * sets the name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * gets the licensee ID
     *
     * @return string|null
     */
    public function getLicenseeId(): ?string
    {
        return $this->licenseeId;
    }

    /**
     * sets the licensee ID
     *
     * @param string $licenseeId licensee ID
     */
    public function setLicenseeId(string $licenseeId): void
    {
        $this->licenseeId = $licenseeId;
    }

    /**
     * @return integer|null
     */
    public function getImported(): ?int
    {
        return $this->imported;
    }

    /**
     * @param integer $imported
     */
    public function setImported(?int $imported): void
    {
        $this->imported = $imported;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string|null
     */
    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    /**
     * @param string $publisher
     */
    public function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string|null
     */
    public function getOriginalname(): ?string
    {
        return $this->originalname;
    }

    /**
     * @param string $originalname
     */
    public function setOriginalname(string $originalname): void
    {
        $this->originalname = $originalname;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string|null
     */
    public function getTopicId(): ?string
    {
        return $this->topicId;
    }

    /**
     * @param string $topicId
     */
    public function setTopicId(string $topicId): void
    {
        $this->topicId = $topicId;
    }
}
