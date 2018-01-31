<?php
/* Copyright (C) 2018 Michael Giesler
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

namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class TextnodeHitch
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\TextnodeHitchRepository")
 */
class TextnodeHitch
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public const MAXIMUM_COUNT = 8;

    /**
     * @var string
     * @MongoDB\Id
     */
    private $id;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $description;

    /**
     * @var Textnode
     * @MongoDB\ReferenceOne(targetDocument="Textnode", inversedBy="childHitches")
     */
    private $sourceTextnode;

    /**
     * @var Textnode
     * @MongoDB\ReferenceOne(targetDocument="Textnode", inversedBy="parentHitches")
     */
    private $targetTextnode;

    /**
     * @var int
     * @MongoDB\Field(type="integer")
     */
    private $status;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Textnode
     */
    public function getTargetTextnode(): Textnode
    {
        return $this->targetTextnode;
    }

    /**
     * @param Textnode $targetTextnode
     */
    public function setTargetTextnode(Textnode $targetTextnode): void
    {
        $this->targetTextnode = $targetTextnode;
    }

    /**
     * @return Textnode
     */
    public function getSourceTextnode(): Textnode
    {
        return $this->sourceTextnode;
    }

    /**
     * @param Textnode $sourceTextnode
     */
    public function setSourceTextnode(Textnode $sourceTextnode): void
    {
        $this->sourceTextnode = $sourceTextnode;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
}
