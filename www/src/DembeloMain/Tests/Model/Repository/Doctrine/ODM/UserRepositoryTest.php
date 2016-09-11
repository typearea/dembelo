<?php

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
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new UserRepository($dm, $uow, $class);
        $user = $repository->save(new User());

        $this->assertInstanceOf(User::class, $user);
    }

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
