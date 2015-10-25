<?php
/* Copyright (C) 2015 Stephan Kreutzer
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


namespace DataManagementDomain\Licensee;

/**
 * Licensee
 */
class Licensee
{
    protected $id;
    protected $name;

    /**
     * Constructor.
     * @param LicenseeId $id   Unique identifier.
     * @param string     $name Name of the Licensee.
     */
    public function __construct(LicenseeId $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Gets the unique identifier of this Licensee.
     * @return LicenseeId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the name of the Licensee.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
