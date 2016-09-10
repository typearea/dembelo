<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class TopicRepository extends DocumentRepository implements TopicRepositoryInterface
{

    /**
     * @inheritdoc
     */
    public function save(Topic $topic)
    {
        $this->dm->persist($topic);
        $this->dm->flush();
        return $topic;
    }

    /**
     * Find all active topics
     * @return Topic[]
     */
    public function findByStatusActive()
    {
        return $this->findBy(array('status' => Topic::STATUS_ACTIVE));
    }
}
