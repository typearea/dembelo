<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Readpath;
use DembeloMain\Model\Repository\Doctrine\ODM\ReadPathRepository;

/**
 * Class ReadPathRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class ReadPathRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new ReadPathRepository($dm, $uow, $class);
        $readPath = $repository->save(new Readpath());

        $this->assertInstanceOf(Readpath::class, $readPath);
    }
}
