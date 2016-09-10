<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class TextNodeRepository extends DocumentRepository implements TextNodeRepositoryInterface
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
