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

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
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
     * @param Importfile $importfile
     * @param string     $twineId
     * @return Textnode
     */
    public function findByTwineId($importfile, $twineId)
    {
        $textnodes = $this->findBy(
            array(
                'importfileId' => new MongoId($importfile->getId()),
                'twineId'      => $twineId,
            )
        );

        if (!is_array($textnodes) || count($textnodes) === 0) {
            return null;
        }

        return $textnodes[0];
    }

    /**
     * sets textnodes to status=inactive that are not in $existingTextnodeIds
     * @param array $existingTextnodeIds array of textnodeIds
     */
    public function disableOrphanedNodes(array $existingTextnodeIds)
    {
        $this->getDocumentManager()->createQueryBuilder(Textnode::class)
            ->update()
            ->field('id')->notIn($existingTextnodeIds)
            ->field('status')->set(Textnode::STATUS_INACTIVE)
            ->multiple(true)
            ->getQuery()
            ->execute();
    }
}
