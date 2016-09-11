<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Story;

/**
 * Interface StoryRepositoryInterface
 * @package DembeloMain\Model\Repository
 */
interface StoryRepositoryInterface
{
    /**
     * Find a story by id
     * @param string $id
     * @return Story
     */
    public function find($id);

    /**
     * Find all stories
     * @return Story[]
     */
    public function findAll();

    /**
     * Save a story
     * @param Story $story
     * @return Story
     */
    public function save(Story $story);
}
