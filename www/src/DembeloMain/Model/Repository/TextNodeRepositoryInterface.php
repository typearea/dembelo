<?php

/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
 *
 * This file is part of Dembelo.
 *
 * Dembelo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Dembelo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Dembelo. If not, see <http://www.gnu.org/licenses/>.
 */

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Interface TextNodeRepositoryInterface
 * @package DembeloMain\Model\Repository
 */
interface TextNodeRepositoryInterface extends ObjectRepository
{
    /**
     * Find a text node by id
     * @param string $id
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
    public function save($textNode);

    /**
     * finds all textnodes of an importfile
     * @param string $importfileId
     * @return Textnode[]
     */
    public function findByImportfileId($importfileId);

    /**
     * finds a textnode by twineId
     * @param Importfile $importfile
     * @param string     $twineId
     * @return Textnode
     */
    public function findByTwineId(Importfile $importfile, $twineId);

    /**
     * disables textnodes that were not found in importfile
     * @param Importfile $importfile
     * @param array      $existingTextnodeIds
     * @return void
     */
    public function disableOrphanedNodes(Importfile $importfile, array $existingTextnodeIds);

    /**
     * finds a textnode by arbitraryId
     * @param string $arbitraryId
     * @return Textnode
     */
    public function findOneActiveByArbitraryId($arbitraryId);

    /**
     * sets the hyphenated version of the text content to $textnode
     * @param Textnode $textnode
     * @return void
     */
    public function setHyphenatedText(Textnode $textnode);

    /**
     * gets the textnode object for the read controller action
     * @param string $topicId
     * @return Textnode
     */
    public function getTextnodeToRead($topicId);

    /**
     * @param string $id
     * @return Textnode|null
     */
    public function findOneActiveById(string $id): ?Textnode;
}
