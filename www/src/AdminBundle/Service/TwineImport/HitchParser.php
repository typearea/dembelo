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
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Exception;

class HitchParser
{
    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var array
     */
    private $nodeNameMapping;

    public function __construct(TextNodeRepositoryInterface $textNodeRepository)
    {
        $this->textnodeRepository = $textNodeRepository;
    }

    public function setNodeNameMapping(array $nodeNameMapping): void
    {
        $this->nodeNameMapping = $nodeNameMapping;
    }

    public function parseDoubleArrowRight(string $content, string $twineName, string $name): array
    {
        $contentArray = explode("-->", $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new Exception("The Twine archive file contains a '".$name."' with the invalid element '[[".$contentArray[0]."-->".$contentArray[1]."]]'.");
        }

        $externalTextnode = $this->textnodeRepository->find($contentArray[1]);

        if (null === $externalTextnode) {
            throw new Exception("There is a textnode named '".$twineName."' which references the external Dembelo Textnode '".$contentArray[1]."', but a Dembelo Textnode with such an Id doesn't exist.");
        }

        $hitch = array();
        $hitch['description'] = $contentArray[0];
        $hitch['textnodeId'] = $contentArray[1];
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        return $hitch;
    }

    public function parseSingleArrowRight(string $content, string $name): array
    {
        $contentArray = explode("->", $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new Exception("The Twine archive file contains a '".$name."' with the invalid element '[[".$contentArray[0]."->".$contentArray[1]."]]'.");
        }

        if (array_key_exists($contentArray[1], $this->nodeNameMapping) !== true) {
            throw new Exception("There is a textnode which references another textnode named '".$contentArray[1]."', but this textnode doesn't exist within the same story.");
        }

        $hitch = array();
        $hitch['description'] = $contentArray[0];
        $hitch['textnodeId'] = $this->nodeNameMapping[$contentArray[1]];
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        return $hitch;
    }

    public function parseSingleArrowLeft(string $content, string $name): array
    {
        $contentArray = explode("<-", $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new Exception("The Twine archive file contains a '".$name."' with the invalid element '[[".$contentArray[0]."<-".$contentArray[1]."]]'.");
        }

        if (array_key_exists($contentArray[0], $this->nodeNameMapping) !== true) {
            throw new Exception("There is a textnode in the Twine archive file which references another textnode named '".$contentArray[0]."', but this textnode doesn't exist within the same story.");
        }

        $hitch = array();
        $hitch['description'] = $contentArray[1];
        $hitch['textnodeId'] = $this->nodeNameMapping[$contentArray[0]];
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        return $hitch;
    }

    public function parseSimpleHitch(string $content, string $name): array
    {
        if (strlen($content) <= 0) {
            throw new Exception("The Twine archive file contains a '".$name."' with the invalid element '[[".$content."]]'.");
        }

        if (array_key_exists($content, $this->nodeNameMapping) !== true) {
            throw new Exception("There is a textnode in the Twine archive file which references another textnode named '".$content."', but this textnode doesn't exist within the same story.");
        }

        $hitch = array();
        $hitch['description'] = $content;
        $hitch['textnodeId'] = $this->nodeNameMapping[$content];
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;

        return $hitch;
    }
}