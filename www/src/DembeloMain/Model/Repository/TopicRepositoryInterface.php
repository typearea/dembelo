<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Topic;

interface TopicRepositoryInterface
{

    /**
     * Find a topic by id
     * @param $id
     * @return Topic
     */
    public function find($id);

    /**
     * Find all topics
     * @return Topic[]
     */
    public function findAll();

    /**
     * Find all active topics
     * @return Topic[]
     */
    public function findByStatusActive();

    /**
     * Save topic
     * @param Topic $topic
     * @return Topic
     */
    public function save(Topic $topic);
}
