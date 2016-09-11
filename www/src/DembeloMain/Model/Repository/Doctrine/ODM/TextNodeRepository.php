<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TextNodeRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class TextNodeRepository extends DocumentRepository implements TextNodeRepositoryInterface
{

    /**
     * Save text node
     * @param Textnode $textNode
     * @return Textnode
     */
    public function save(Textnode $textNode)
    {
        $this->dm->persist($textNode);
        $this->dm->flush();

        return $textNode;
    }
}
