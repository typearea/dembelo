<?php
/* Copyright (C) 2017 Michael Giesler
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

namespace DembeloMain\Model;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\User;
use DembeloMain\Model\Repository\ReadPathRepositoryInterface;
use DembeloMain\Document\Readpath as ReadpathDocument;

/**
 * Class Readpath
 * @package DembeloMain\Model
 */
class Readpath
{
    private $readpathRepository;

    /**
     * Readpath constructor.
     * @param ReadPathRepositoryInterface $readpathRepository
     */
    public function __construct(ReadPathRepositoryInterface $readpathRepository)
    {
        $this->readpathRepository = $readpathRepository;
    }

    /**
     * saves a new readpath node to database
     *
     * @param Textnode  $textnode
     * @param User|null $user
     */
    public function storeReadpath(Textnode $textnode, User $user = null)
    {
        if (is_null($user)) {
            return;
        }

        $readpath = new ReadpathDocument();
        $readpath->setTextnodeId($textnode->getId());
        $readpath->setUserId($user->getId());
        $readpath->setTimestamp(new \MongoDate(time()));

        $this->readpathRepository->save($readpath);
    }
}
