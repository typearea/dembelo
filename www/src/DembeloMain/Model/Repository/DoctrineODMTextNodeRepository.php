<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Textnode;
use Doctrine\ODM\MongoDB\DocumentRepository;

class DoctrineODMTextNodeRepository extends DocumentRepository implements TextNodeRepositoryInterface
{

    /**
     * @inheritdoc
     */
    public function save(Textnode $textNode)
    {
        $this->dm->persist($textNode);
        $this->dm->flush();
        return $textNode;
    }
}
