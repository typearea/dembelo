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
use DembeloMain\Document\Topic;
use DembeloMain\Document\User;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class FavoriteManager
 * @package DembeloMain\Model
 */
class FavoriteManager
{
    /* @var Session */
    private $session;

    /* @var TextNodeRepositoryInterface */
    private $textNodeRepository;

    /**
     * FavoriteManager constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * sets a favorite textnode for a topic
     * @param Textnode  $textnode
     * @param User|null $user
     */
    public function setFavorite(Textnode $textnode, User $user = null)
    {
        if (is_null($user)) {
            $this->session->set('favorite_'.$textnode->getTopicId(), $textnode->getArbitraryId());

            return;
        }
        $user->setFavorite($textnode->getTopicId(), $textnode->getArbitraryId());
    }

    /**
     * gets a favorite textnode for a topic
     * @param Topic     $topic
     * @param User|null $user
     * @return string
     */
    public function getFavorite(Topic $topic, User $user = null)
    {
        if (is_null($user)) {
            return $this->session->get('favorite_'.$topic->getId());
        }

        return $user->getFavorite($topic->getId());
    }
}
