<?php
/* Copyright (C) 2017 Michael Giesler
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
namespace AdminBundle\Service\TwineImport;

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;

/**
 * Class ParserContext
 */
class ParserContext
{
    /**
     * @var Importfile
     */
    private $importfile;

    /**
     * @var bool
     */
    private $twineRelevant = false;

    /**
     * @var Textnode
     */
    private $currentTextnode;

    /**
     * @var bool
     */
    private $twineText = false;

    /**
     * twineId => $textnode
     * @var Textnode[]
     */
    private $textnodeMapping = [];

    /**
     * @var int
     */
    private $twineStartnodeId;

    /**
     * @var bool
     */
    private $accessSet = false;

    /**
     * textnodename => textnode
     * @var Textnode[]
     */
    private $nodenameMapping = [];

    /**
     * @param Importfile $importfile
     *
     * @return void
     *
     * @throws \Exception
     */
    public function init(Importfile $importfile): void
    {
        $this->importfile = $importfile;

        if (null === $this->importfile->getLicenseeId()) {
            throw new \Exception('no licensee available');
        }

        if (null === $this->importfile->getFilename()) {
            throw new \Exception('no filename available');
        }
    }

    /**
     * @return Importfile
     */
    public function getImportfile(): Importfile
    {
        return $this->importfile;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->importfile->getFilename();
    }

    /**
     * @return bool
     */
    public function isTwineRelevant(): bool
    {
        return $this->twineRelevant;
    }

    /**
     * @param bool $twineRelevant
     *
     * @return void
     */
    public function setTwineRelevant(bool $twineRelevant): void
    {
        $this->twineRelevant = $twineRelevant;
    }

    /**
     * @return Textnode|null
     */
    public function getCurrentTextnode(): ?Textnode
    {
        return $this->currentTextnode;
    }

    /**
     * @param Textnode $currentTextnode
     *
     * @return void
     */
    public function setCurrentTextnode(Textnode $currentTextnode): void
    {
        $this->currentTextnode = $currentTextnode;
        $twineId = $currentTextnode->getTwineId();
        $this->textnodeMapping[$twineId] = $currentTextnode;
    }

    /**
     * @return bool
     */
    public function isTwineText(): bool
    {
        return $this->twineText;
    }

    /**
     * @param bool $twineText
     *
     * @return void
     */
    public function setTwineText(bool $twineText): void
    {
        $this->twineText = $twineText;
    }

    /**
     * @return void
     */
    public function clearTextnodeMapping(): void
    {
        $this->textnodeMapping = [];
    }

    /**
     * @return Textnode[]
     */
    public function getTextnodeMapping(): array
    {
        return $this->textnodeMapping;
    }

    /**
     * @return int
     */
    public function getTwineStartnodeId(): ?int
    {
        return $this->twineStartnodeId;
    }

    /**
     * @param mixed $twineStartnodeId
     *
     * @return void
     */
    public function setTwineStartnodeId(int $twineStartnodeId): void
    {
        $this->twineStartnodeId = $twineStartnodeId;
    }

    /**
     * @return bool
     */
    public function isAccessSet(): bool
    {
        return $this->accessSet;
    }

    /**
     * @param bool $accessSet
     *
     * @return void
     */
    public function setAccessSet(bool $accessSet): void
    {
        $this->accessSet = $accessSet;
    }

    /**
     * @return Textnode[]
     */
    public function getNodenameMapping(): array
    {
        return $this->nodenameMapping;
    }

    /**
     * @param Textnode[] $nodenameMapping
     *
     * @return void
     */
    public function setNodenameMapping(array $nodenameMapping): void
    {
        $this->nodenameMapping = $nodenameMapping;
    }
}
