<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Readpath;
use DembeloMain\Model\Repository\ReadPathRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class ReadPathRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class ReadPathRepository extends DocumentRepository implements ReadPathRepositoryInterface
{

    /**
     * Save a read path
     * @param Readpath $readPath
     * @return Readpath
     */
    public function save(Readpath $readPath)
    {
        $this->dm->persist($readPath);
        $this->dm->flush();

        return $readPath;
    }
}
