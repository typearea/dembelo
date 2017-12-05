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
 */

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository extends DocumentRepository
{
    /**
     * Save object
     * @param object $object
     *
     * @return object
     */
    public function save($object)
    {
        $this->beforeSave($object);
        $this->dm->persist($object);
        $this->dm->flush();

        return $object;
    }

    /**
     * @param misc $object
     *
     * @return void
     */
    protected function beforeSave($object)
    {
    }
}
