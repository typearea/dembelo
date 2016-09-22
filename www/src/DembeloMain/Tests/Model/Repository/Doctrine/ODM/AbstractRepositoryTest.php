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

use DembeloMain\Model\Repository\Doctrine\ODM\AbstractRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Persisters\DocumentPersister;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * Class AbstractRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
abstract class AbstractRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test save
     */
    public function testSave()
    {
        $repository = $this->getAbstractRepositoryMock();
        $object = new \stdClass();
        $user = $repository->save($object);
        $this->assertSame($object, $user);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DocumentManager
     */
    protected function getDocumentManagerMock()
    {
        return $this->getMockBuilder(DocumentManager::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UnitOfWork
     */
    protected function getUnitOfWorkMock()
    {
        return $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClassMetadata
     */
    protected function getClassMock()
    {
        return $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DocumentPersister
     */
    protected function getDocumentPersisterMock()
    {
        return $this->getMockBuilder(DocumentPersister::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractRepository
     */
    private function getAbstractRepositoryMock()
    {
        return $this->getMockForAbstractClass(
            AbstractRepository::class,
            array($this->getDocumentManagerMock(), $this->getUnitOfWorkMock(), $this->getClassMock())
        );
    }
}
