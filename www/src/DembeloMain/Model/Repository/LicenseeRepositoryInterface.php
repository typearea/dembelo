<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Licensee;

/**
 * Interface LicenseeRepositoryInterface
 * @package DembeloMain\Model\Repository
 */
interface LicenseeRepositoryInterface
{
    /**
     * Find a licensee by id
     * @param string $id
     * @return Licensee
     */
    public function find($id);

    /**
     * Find all licensees
     * @return Licensee[]
     */
    public function findAll();

    /**
     * Save a licensee
     * @param Licensee $licensee
     * @return Licensee
     */
    public function save(Licensee $licensee);
}
