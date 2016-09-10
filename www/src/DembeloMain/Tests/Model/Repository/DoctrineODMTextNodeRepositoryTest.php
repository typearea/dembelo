<?php

namespace DembeloMain\Tests\Model\Repository;

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\DoctrineODMTextNodeRepository;

class DoctrineODMTextNodeRepositoryTest extends AbstractDoctrineODMRepositoryTest
{
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new DoctrineODMTextNodeRepository($dm, $uow, $class);
        $textNode = new Textnode();
        $textNode = $repository->save($textNode);

        $this->assertInstanceOf(Textnode::class, $textNode);
    }
}
