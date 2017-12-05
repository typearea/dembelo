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
     * @param HitchParser                 $hitchParser
     * @param TextNodeRepositoryInterface $textnodeRepository
     */
    public function __construct(HitchParser $hitchParser, TextNodeRepositoryInterface $textnodeRepository)
    {
        $this->hitchParser = $hitchParser;
        $this->textnodeRepository = $textnodeRepository;
    }

    /**
     * @param ParserContext $parserContext
     *
     * @return void
     */
    public function setParserContext(ParserContext $parserContext): void
    {
        $this->parserContext = $parserContext;
    }

    /**
     * @param string $name
     * @param array  $attributes
     *
     * @return void
     *
     * @throws \Exception
     */
    public function startElement(string $name, array $attributes): void
    {
        if (!$this->checkElementStoryData($name, $attributes)) {
            return;
        }

        $this->parserContext->setTwineStartnodeId((int) $attributes['startnode']);
        $this->parserContext->clearTextnodeMapping();
        $this->parserContext->setTwineRelevant(true);
    }

    /**
     * @param string $name
     *
     * @return void
     *
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
     * @param string $name
     * @param string $dembeloId
     *
     * @return void
     *
     * @throws \Exception
     */
    private function endElementForOneTextnode(string $name, string $dembeloId): void
    {
        $textnode = $this->textnodeRepository->find($dembeloId);

        if (null === $textnode) {
            throw new \Exception(sprintf('The Dembelo Textnode with Id \'%s\' doesn\'t exist, but should by now.', $dembeloId));
        }

        $textnodeText = $textnode->getText();
        $textnodeTextNew = $this->parseText($textnode, $textnodeText, $name);

        if (null !== $textnodeTextNew) {
            $textnodeTextNew = $this->convertToPTags($textnodeTextNew);
        }

        $textnode->setText($textnodeTextNew);
        $this->textnodeRepository->setHyphenatedText($textnode);
    }

    /**
     * @param string $name
     * @param array  $attributes
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function checkElementStoryData(string $name, array $attributes): bool
    {
        if ($this->parserContext->isTwineRelevant()) {
            throw new \Exception(sprintf("Nested '%s' found in Twine archive file '%s'.", $name, $this->parserContext->getFilename()));
        }

        if (!isset($attributes['startnode']) || !is_numeric($attributes['startnode'])) {
            return false;
        }

        if (isset($attributes['name']) !== true) {
            throw new \Exception(sprintf("There is a '%s' in the Twine archive file '%s' which is missing its 'name' attribute.", $name, $this->parserContext->getFilename()));
        }

        return true;
    }

    /**
     * @param string $textnodeText
     *
     * @return string
     */
    private function convertToPTags(string $textnodeText): string
    {
        $textnodeTextLength = strlen($textnodeText);
        $textnodeTextNew = '<p>';
        $consumed = 0;
        for ($i = 0; $i < $textnodeTextLength; ++$i) {
            if ($textnodeText[$i] === "\n" || $textnodeText[$i] === "\r") {
                ++$consumed;

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

    /**
     * @param Textnode $textnode
     * @param string   $text
     * @param string   $name
     *
     * @return null|string
     */
    private function parseText(Textnode $textnode, string $text, string $name): ?string
    {
        $textnodeTextNew = preg_replace_callback(
            '/\[\[(.*?)\]\]/',
            function ($matches) use ($textnode, $name) {
                $content = $matches[1];
                $hitch = null;
                $metadata = null;

                $this->hitchParser->setNodeNameMapping($this->parserContext->getNodenameMapping());

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
     * @param Textnode   $textnode
     * @param array|null $hitch
     *
     * @return void
     *
     * @throws \Exception
     */
    private function appendHitchToTextnode(Textnode $textnode, ?array $hitch): void
    {
        if (null === $hitch) {
            return;
        }
        if ($textnode->getHitchCount() >= Textnode::HITCHES_MAXIMUM_COUNT) {
            throw new \Exception(sprintf('There is a textnode in the Twine archive file which has more than %d links.', Textnode::HITCHES_MAXIMUM_COUNT));
        }

        if ($textnode->appendHitch($hitch) !== true) {
            throw new \Exception('Failed to append hitch for a textnode');
        }
    }

    /**
     * @param Textnode $textnode
     * @param string   $content
     * @param string   $name
     *
     * @return array
     *
     * @throws \Exception
     */
    private function parseColonArrows(Textnode $textnode, string $content, string $name): array
    {
        $contentArray = explode('>:<', $content, 2);

        if (strlen($contentArray[0]) <= 0 || strlen($contentArray[1]) <= 0) {
            throw new \Exception(sprintf('The Twine archive file contains a \'%s\' with the invalid element \'[[%s>:<%s]]\'.', $name, $contentArray[0], $contentArray[1]));
        }

        $metadata = $textnode->getMetadata() ?? [];

        if (array_key_exists($contentArray[0], $metadata) === true) {
            throw new \Exception(sprintf('There is a textnode in the Twine archive file which contains the metadata field \'%s\' twice or would overwrite the already existing value of that field.', $contentArray[0]));
        }

        $metadata[$contentArray[0]] = $contentArray[1];

        return $metadata;
    }
}
