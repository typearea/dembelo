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
 */


/**
 * @package DembeloMain
 */

namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Licensee
 *
 * @MongoDB\Document
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\LicenseeRepository")
 */
class Licensee
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
}
