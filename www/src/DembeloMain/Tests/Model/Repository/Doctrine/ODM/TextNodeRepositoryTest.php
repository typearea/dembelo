<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;

class TextNodeRepositoryTest extends AbstractRepositoryTest
{
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new TextNodeRepository($dm, $uow, $class);
        $textNode = new Textnode();
        $textNode = $repository->save($textNode);

        $this->assertInstanceOf(Textnode::class, $textNode);
    }
}
