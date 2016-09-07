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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Importfile
 *
 * @MongoDB\Document
 */
class Importfile
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $name;

    /**
     * @MongoDB\ObjectId
     */
    protected $licenseeId;

    /**
     * @MongoDB\Timestamp
     */
    protected $imported;

    /**
     * @MongoDB\String
     */
    protected $author;

    /**
     * @MongoDB\String
     */
    protected $publisher;

    /**
     * @MongoDB\String
     */
    protected $orgname;

    /**
     * @MongoDB\String
     */
    protected $filename;

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
     * gets the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * sets the name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * gets the licensee ID
     *
     * @return string
     */
    public function getLicenseeId()
    {
        return $this->licenseeId;
    }

    /**
     * sets the licensee ID
     *
     * @param string $licenseeId licensee ID
     */
    public function setLicenseeId($licenseeId)
    {
        $this->licenseeId = $licenseeId;
    }

    /**
     * @return integer
     */
    public function getImported()
    {
        return $this->imported;
    }

    /**
     * @param integer $imported
     */
    public function setImported($imported)
    {
        $this->imported = $imported;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param string $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string
     */
    public function getOrgname()
    {
        return $this->orgname;
    }

    /**
     * @param string $orgname
     */
    public function setOrgname($orgname)
    {
        $this->orgname = $orgname;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
