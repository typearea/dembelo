<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TopicRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class TopicRepository extends DocumentRepository implements TopicRepositoryInterface
{

    /**
     * Save topic
     * @param Topic $topic
     * @return Topic
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
