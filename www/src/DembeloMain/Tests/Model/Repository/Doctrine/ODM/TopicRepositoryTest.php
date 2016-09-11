<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use Doctrine\MongoDB\ArrayIterator;

/**
 * Class TopicRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class TopicRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new TopicRepository($dm, $uow, $class);
        $topic = new Topic();
        $topic = $repository->save($topic);

        $this->assertInstanceOf(Topic::class, $topic);
    }

    /**
     * Test find topics with status active
     */
    public function testFindByStatusActive()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();
        $documentPersister = $this->getDocumentPersisterMock();

        $collection = new ArrayIterator(array(new Topic()));
        $documentPersister->expects($this->once())->method('loadAll')->willReturn($collection);
        $uow->expects($this->once())->method('getDocumentPersister')->willReturn($documentPersister);

        $repository = new TopicRepository($dm, $uow, $class);
        $topics = $repository->findByStatusActive();

        $this->assertInstanceOf(Topic::class, $topics[0]);
    }
}
