<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Story;
use DembeloMain\Model\Repository\StoryRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class StoryRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class StoryRepository extends DocumentRepository implements StoryRepositoryInterface
{

    /**
     * Save story
     * @param Story $story
     * @return story
     */
    public function save(Story $story)
    {
        $this->dm->persist($story);
        $this->dm->flush();

        return $story;
    }
}
