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

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TextNodeRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class TextNodeRepository extends DocumentRepository implements TextNodeRepositoryInterface
{

    /**
     * Save text node
     * @param Textnode $textNode
     * @return Textnode
     */
    public function save(Textnode $textNode)
    {
        $this->dm->persist($textNode);
        $this->dm->flush();

        return $textNode;
    }
}
