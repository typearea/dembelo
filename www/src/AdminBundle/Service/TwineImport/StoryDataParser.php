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

/**
 * Class StoryDataParser
 * @package AdminBundle\Service\TwineImport
 */
class StoryDataParser
{
    /**
     * @var ParserContext
     */
    private $parserContext;

    /**
     * @var HitchParser
     */
    private $hitchParser;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * StoryDataParser constructor.
     * @param HitchParser $hitchParser
     * @param TextNodeRepositoryInterface $textnodeRepository
     */
    public function __construct(HitchParser $hitchParser, TextNodeRepositoryInterface $textnodeRepository)
    {
        $this->hitchParser = $hitchParser;
        $this->textnodeRepository = $textnodeRepository;
    }

    /**
     * @param ParserContext $parserContext
     * @return void
     */
    public function setParserContext(ParserContext $parserContext): void
    {
        $this->parserContext = $parserContext;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @throws \Exception
     */
    public function startElement(string $name, array $attributes): void
    {
        if (!$this->checkElementStoryData($name, $attributes)) {
            return;
        }

        $this->parserContext->setTwineStartnodeId((int)$attributes['startnode']);
        $this->parserContext->clearTextnodeMapping();
        $this->parserContext->setTwineRelevant(true);
    }

    /**
     * @param string $name
     * @return void
     * @throws \Exception
     */
    public function endElement(string $name): void
    {
        foreach ($this->parserContext->getTextnodeMapping() as $dembeloId) {
            $this->endElementForOneTextnode($name, $dembeloId);
        }

        $this->textnodeRepository->disableOrphanedNodes($this->parserContext->getImportfile(), array_values($this->parserContext->getTextnodeMapping()));

        $this->parserContext->setTwineRelevant(false);
        $this->parserContext->setTwineStartnodeId(-1);
        $this->parserContext->clearTextnodeMapping();
        $this->parserContext->setAccessSet(false);
    }

    /**
     * @param $name
     * @param $dembeloId
     * @throws \Exception
     */
    private function endElementForOneTextnode(string $name, string $dembeloId): void
    {
        $textnode = $this->textnodeRepository->find($dembeloId);

        if (null === $textnode) {
            throw new \Exception('The Dembelo Textnode with Id \''.$dembeloId.'\' doesn\'t exist, but should by now.');
        }

        $textnodeText = $textnode->getText();
        $textnodeTextNew = $this->parseText($textnode, $textnodeText, $name);

        if (null !== $textnodeTextNew) {
            $textnodeTextNew = $this->convertToPTags($textnodeTextNew);
        }

        $textnode->setText($textnodeTextNew);
        $this->textnodeRepository->setHyphenatedText($textnode);
    }

    private function checkElementStoryData(string $name, array $attributes): bool
    {
        if ($this->parserContext->isTwineRelevant()) {
            throw new \Exception("Nested '".$name."' found in Twine archive file '".$this->parserContext->getFilename()."'.");
        }

        if (!isset($attributes['startnode']) || !is_numeric($attributes['startnode'])) {
            return false;
        }

        if (isset($attributes['name']) !== true) {
            throw new \Exception("There is a '".$name."' in the Twine archive file '".$this->parserContext->getFilename()."' which is missing its 'name' attribute.");
        }

        return true;
    }

    private function convertToPTags(string $textnodeText): string
    {
        $textnodeTextLength = strlen($textnodeText);
        $textnodeTextNew = '<p>';
        $consumed = 0;
        for ($i = 0; $i < $textnodeTextLength; $i++) {
            if ($textnodeText[$i] === "\n" || $textnodeText[$i] === "\r") {
                $consumed++;

                continue;
            }
            if ($consumed > 0 && $i > $consumed) {
                $textnodeTextNew .= '</p><p>';
            }

            $textnodeTextNew .= $textnodeText[$i];
            $consumed = 0;
        }

        $textnodeTextNew .= '</p>';

        return $textnodeTextNew;
    }

    private function parseText(Textnode $textnode, string $text, string $name): ?string
    {
        $textnodeTextNew = preg_replace_callback(
            '/\[\[(.*?)\]\]/',
            function ($matches) use ($textnode, $name) {
                $content = $matches[1];
                $hitch = null;
                $metadata = null;
                if (strpos($content, '-->') !== false) {
                    $hitch = $this->hitchParser->parseDoubleArrowRight($content, $name);
                } elseif (strpos($content, '->') !== false) {
                    $hitch = $this->hitchParser->parseSingleArrowRight($content, $name);
                } elseif (strpos($content, '<-') !== false) {
                    $hitch = $this->hitchParser->parseSingleArrowLeft($content, $name);
                } elseif (strpos($content, '>:<') !== false) {
                    $metadata = $this->parseColonArrows($textnode, $content, $name);
                    $textnode->setMetadata($metadata);
                } else {
                    $hitch = $this->hitchParser->parseSimpleHitch($content, $name);
                }

                $this->appendHitchToTextnode($textnode, $hitch);
            },
            $text
        );

        return trim($textnodeTextNew);
    }

    /**
     * @param Textnode $textnode
     * @param array|null $hitch
     * @throws \Exception
     */
    private function appendHitchToTextnode(Textnode $textnode, ?array $hitch): void
    {
        if ($hitch === null) {
            return;
        }
        if ($textnode->getHitchCount() >= Textnode::HITCHES_MAXIMUM_COUNT) {
            throw new \Exception('There is a textnode in the Twine archive file which has more than '.Textnode::HITCHES_MAXIMUM_COUNT.' links.');
        }

        if ($textnode->appendHitch($hitch) !== true) {
            throw new \Exception('Failed to append hitch for a textnode');
        }
    }

    /**
     * @param Textnode $textnode
     * @param string $content
     * @param string $name
     * @return array
     * @throws \Exception
     */
    private function parseColonArrows(Textnode $textnode, string $content, string $name): array
    {
        $contentArray = explode('>:<', $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new \Exception('The Twine archive file contains a \''.$name.'\' with the invalid element \'[['.$contentArray[0].'>:<'.$contentArray[1].']]\'.');
        }

        $metadata = $textnode->getMetadata() ?? [];

        if (array_key_exists($contentArray[0], $metadata) === true) {
            throw new \Exception('There is a textnode in the Twine archive file which contains the metadata field \''.$contentArray[0].'\' twice or would overwrite the already existing value of that field.');
        }

        $metadata[$contentArray[0]] = $contentArray[1];

        return $metadata;
    }
}