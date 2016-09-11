<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

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
     * @return \PHPUnit_Framework_MockObject_MockObject|DocumentManager
     */
    protected function getDocumentManagerMock()
    {
        return $this->getMockBuilder(DocumentManager::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClassMetadata
     */
    protected function getClassMock()
    {
        return $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UnitOfWork
     */
    protected function getUnitOfWorkMock()
    {
        return $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DocumentPersister
     */
    protected function getDocumentPersisterMock()
    {
        return $this->getMockBuilder(DocumentPersister::class)->disableOriginalConstructor()->getMock();
    }
}
