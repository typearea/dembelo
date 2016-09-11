<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Licensee;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class LicenseeRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class LicenseeRepository extends DocumentRepository implements LicenseeRepositoryInterface
{

    /**
     * Save licensee
     * @param Licensee $licensee
     * @return Licensee
     */
    public function save(Licensee $licensee)
    {
        $this->dm->persist($licensee);
        $this->dm->flush();

        return $licensee;
    }
}
