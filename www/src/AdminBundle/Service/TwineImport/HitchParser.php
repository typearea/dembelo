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

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Exception;

/**
 * Class HitchParser
 */
class HitchParser
{
    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var Textnode[]
     */
    private $nodeNameMapping;

    /**
     * HitchParser constructor.
     * @param TextNodeRepositoryInterface $textNodeRepository
     */
    public function __construct(TextNodeRepositoryInterface $textNodeRepository)
    {
        $this->textnodeRepository = $textNodeRepository;
    }

    /**
     * @param Textnode[] $nodeNameMapping
     */
    public function setNodeNameMapping(array $nodeNameMapping): void
    {
        $this->nodeNameMapping = $nodeNameMapping;
    }

    /**
     * @param string $content
     * @param string $name
     *
     * @return TextnodeHitch
     *
     * @throws Exception
     */
    public function parseDoubleArrowRight(string $content, string $name): TextnodeHitch
    {
        list($description, $textnodeId) = explode('-->', $content, 2);

        if (strlen($description) <= 0 || strlen($textnodeId) <= 0) {
            throw new Exception(sprintf("The Twine archive file contains a '%s' with the invalid element '[[%s-->%s]]'.", $name, $description, $textnodeId));
        }

        $externalTextnode = $this->textnodeRepository->find($textnodeId);

        if (null === $externalTextnode) {
            throw new Exception(sprintf("There is a textnode which references the external Dembelo Textnode '%s', but a Dembelo Textnode with such an Id doesn't exist.", $textnodeId));
        }

        $hitch = new TextnodeHitch();
        $hitch->setDescription($description);
        $hitch->setTargetTextnode($externalTextnode);
        $hitch->setStatus(TextnodeHitch::STATUS_ACTIVE);

        return $hitch;
    }

    /**
     * @param string $content
     * @param string $name
     *
     * @return TextnodeHitch
     *
     * @throws Exception
     */
    public function parseSingleArrowRight(string $content, string $name): TextnodeHitch
    {
        list($description, $nodeName) = explode('->', $content, 2);

        if (\strlen($description) <= 0 || \strlen($nodeName) <= 0) {
            throw new Exception(sprintf("The Twine archive file contains a '%s' with the invalid element '[[%s->%s]]'.", $name, $description, $nodeName));
        }

        if (array_key_exists($nodeName, $this->nodeNameMapping) !== true) {
            throw new Exception(sprintf("There is a textnode which references another textnode named '%s', but this textnode doesn't exist within the same story.", $nodeName));
        }

        $hitch = new TextnodeHitch();
        $hitch->setDescription($description);
        $hitch->setTargetTextnode($this->nodeNameMapping[$nodeName]);
        $hitch->setStatus(TextnodeHitch::STATUS_ACTIVE);

        return $hitch;
    }

    /**
     * @param string $content
     * @param string $name
     *
     * @return TextnodeHitch
     *
     * @throws Exception
     */
    public function parseSingleArrowLeft(string $content, string $name): TextnodeHitch
    {
        list($nodeName, $description) = explode('<-', $content, 2);

        if (\strlen($nodeName) <= 0 || \strlen($description) <= 0) {
            throw new Exception(sprintf("The Twine archive file contains a '%s' with the invalid element '[[%s<-%s]]'.", $name, $nodeName, $description));
        }

        if (array_key_exists($nodeName, $this->nodeNameMapping) !== true) {
            throw new Exception(sprintf("There is a textnode in the Twine archive file which references another textnode named '%s', but this textnode doesn't exist within the same story.", $nodeName));
        }

        $hitch = new TextnodeHitch();
        $hitch->setDescription($description);
        $hitch->setTargetTextnode($this->nodeNameMapping[$nodeName]);
        $hitch->setStatus(TextnodeHitch::STATUS_ACTIVE);

        return $hitch;
    }

    /**
     * @param string $content
     * @param string $name
     *
     * @return TextnodeHitch
     *
     * @throws Exception
     */
    public function parseSimpleHitch(string $content, string $name): TextnodeHitch
    {
        if (strlen($content) <= 0) {
            throw new Exception(sprintf("The Twine archive file contains a '%s' with the invalid element '[[%s]]'.", $name, $content));
        }

        if (array_key_exists($content, $this->nodeNameMapping) !== true) {
            throw new Exception(sprintf("There is a textnode in the Twine archive file which references another textnode named '%s', but this textnode doesn't exist within the same story.", $content));
        }

        $hitch = new TextnodeHitch();
        $hitch->setDescription($content);
        $hitch->setTargetTextnode($this->nodeNameMapping[$content]);
        $hitch->setStatus(TextnodeHitch::STATUS_ACTIVE);

        return $hitch;
    }
}
