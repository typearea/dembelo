<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Story;
use DembeloMain\Model\Repository\Doctrine\ODM\StoryRepository;

/**
 * Class StoryRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class StoryRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new StoryRepository($dm, $uow, $class);
        $story = $repository->save(new Story());

        $this->assertInstanceOf(Story::class, $story);
    }
}
