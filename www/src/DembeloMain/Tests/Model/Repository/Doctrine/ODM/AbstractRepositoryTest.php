<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;

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
}
