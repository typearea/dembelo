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
     * @param Session                     $session
     * @param TextNodeRepositoryInterface $textNodeRepository
     */
    public function __construct(Session $session, TextNodeRepositoryInterface $textNodeRepository)
    {
        $this->session = $session;
        $this->textNodeRepository = $textNodeRepository;
    }

    /**
     * sets a favorite textnode for a topic
     * @param Topic     $topic
     * @param Textnode  $textnode
     * @param User|null $user
     */
    public function setFavorite(Topic $topic, Textnode $textnode, User $user = null)
    {
        if (is_null($user)) {
            $this->session->set('favorite_'.$topic->getId(), $textnode->getId());

            return;
        }
        $user->setFavorite($topic->getId(), $textnode->getId());
    }

    /**
     * gets a favorite textnode for a topic
     * @param Topic     $topic
     * @param User|null $user
     * @return Textnode
     */
    public function getFavorite(Topic $topic, User $user = null)
    {
        if (is_null($user)) {
            $textnodeId = $this->session->get('favorite_'.$topic->getId());
        } else {
            $textnodeId = $user->getFavorite($topic->getId());
        }

        return $this->textNodeRepository->find($textnodeId);
    }
}
