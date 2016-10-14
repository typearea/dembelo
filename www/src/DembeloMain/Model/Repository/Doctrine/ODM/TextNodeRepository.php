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

use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use MongoId;

/**
 * Class TextNodeRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class TextNodeRepository extends AbstractRepository implements TextNodeRepositoryInterface
{
    /**
     * finds textnodes by importfileId
     *
     * @param string $importfileId
     * @return array
     */
    public function findByImportfileId($importfileId)
    {
        return $this->findBy(array('importfileId' => new MongoId($importfileId)));
    }

    /**
     * finds a textnode by importfileId and twineId
     * @param string $importfileId
     * @param string $twineId
     */
    public function findByTwineId($importfileId, $twineId)
    {
        return $this->findBy(
            array(
                'importfileId' => new MongoId($importfileId),
                'twineId'      => $twineId,
            )
        );
    }
}
