<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Readpath;

/**
 * Interface ReadPathRepositoryInterface
 * @package DembeloMain\Model\Repository
 */
interface ReadPathRepositoryInterface
{
    /**
     * Find a read path by id
     * @param string $id
     * @return Readpath
     */
    public function find($id);

    /**
     * Find all read paths
     * @return Readpath[]
     */
    public function findAll();

    /**
     * Save a read path
     * @param Readpath $readPath
     * @return Readpath
     */
    public function save(Readpath $readPath);
}
