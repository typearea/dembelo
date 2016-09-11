<?php

namespace DembeloMain\Tests\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Licensee;
use DembeloMain\Model\Repository\Doctrine\ODM\LicenseeRepository;

/**
 * Class LicenseeRepositoryTest
 * @package DembeloMain\Tests\Model\Repository\Doctrine\ODM
 */
class LicenseeRepositoryTest extends AbstractRepositoryTest
{
    /**
     * Test save
     */
    public function testSave()
    {
        $dm = $this->getDocumentManagerMock();
        $class = $this->getClassMock();
        $uow = $this->getUnitOfWorkMock();

        $repository = new LicenseeRepository($dm, $uow, $class);
        $licensee = $repository->save(new Licensee());

        $this->assertInstanceOf(Licensee::class, $licensee);
    }
}
