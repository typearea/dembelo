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

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\UserRepository;

/**
 * Class UserRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class UserRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test find user by email
     */
    public function testFindByStatusActive()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();
        $documentPersister = $this->getDocumentPersisterMock();

        $documentPersister->expects($this->once())->method('load')->willReturn(new User());
        $uow->expects($this->once())->method('getDocumentPersister')->willReturn($documentPersister);

        $repository = new UserRepository($dm, $uow, $class);
        $user = $repository->findByEmail('max@mustermann.de');

        $this->assertInstanceOf(User::class, $user);
    }
}
