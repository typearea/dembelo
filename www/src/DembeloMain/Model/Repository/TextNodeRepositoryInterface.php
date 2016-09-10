<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Textnode;

interface TextNodeRepositoryInterface
{
    /**
     * Find a text node by id
     * @param $id
     * @return Textnode
     */
    public function find($id);

    /**
     * Find all text nodes
     * @return Textnode[]
     */
    public function findAll();

    /**
     * Save a text node
     * @param Textnode $textNode
     * @return Textnode
     */
    public function save(Textnode $textNode);
}
