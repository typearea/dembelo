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

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\UserRepositoryInterface;

/**
 * Class UserRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * Find a user by email
     * @param string $email
     * @return User
     */
    public function findByEmail($email): ?User
    {
        /** @var User $user */
        $user = $this->findOneBy(array('email' => $email));

        return $user;
    }
}
