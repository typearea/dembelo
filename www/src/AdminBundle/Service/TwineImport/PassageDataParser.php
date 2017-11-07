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
 * Class PassageDataParser
 * @package AdminBundle\Service\TwineImport
 */
class PassageDataParser
{
    /**
     * @var ParserContext
     */
    private $parserContext;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var string
     */
    private $twineTextnodeName;

    /**
     * PassageDataParser constructor.
     * @param TextNodeRepositoryInterface $textNodeRepository
     */
    public function __construct(TextNodeRepositoryInterface $textNodeRepository)
    {
        $this->textnodeRepository = $textNodeRepository;
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
     * @param array  $attrs
     * @throws \Exception
     */
    public function startElement(string $name, array $attrs): void
    {
        if ($this->parserContext->isTwineText()) {
            throw new \Exception("Nested '".$name."' found in Twine archive file '".$this->parserContext->getFilename()."'.");
        }

        if (isset($attrs['pid']) !== true) {
            throw new \Exception("There is a '".$name."' in the Twine archive file '".$this->parserContext->getFilename()."' which is missing its 'pid' attribute.");
        }

        if (is_numeric($attrs['pid']) !== true) {
            throw new \Exception("There is a '".$name."' in the Twine archive file '".$this->parserContext->getFilename()."' which hasn't a numeric value in its 'pid' attribute ('".$attrs['pid']."' was found instead).");
        }

        $twineId = $this->getTwineId($attrs['tags'], $attrs['name']);

        if (array_key_exists($twineId, $this->parserContext->getTextnodeMapping()) === true) {
            throw new \Exception("There is a '".$name."' in the Twine archive file '".$this->parserContext->getFilename()."' which has a non unique 'id' tag [".$twineId."], in node '".$attrs['name']."'");
        }

        $textnode = $this->textnodeRepository->findByTwineId($this->parserContext->getImportfile(), $twineId);
        if (null === $textnode) {
            $textnode = $this->createTextnode($twineId);
        } else {
            $textnode->setText('');
            $textnode->clearHitches();
        }

        $this->twineTextnodeName = $attrs['name'];

        $textnode->setMetadata(
            [
                'Titel' => $this->twineTextnodeName,
                'Autor' => $this->parserContext->getImportfile()->getAuthor(),
                'Verlag' => $this->parserContext->getImportfile()->getPublisher(),
            ]
        );

        if ((int) $attrs['pid'] === $this->parserContext->getTwineStartnodeId()) {
            if (!$this->parserContext->isAccessSet()) {
                $textnode->setAccess(true);
                $this->parserContext->setAccessSet(true);
            } else {
                throw new \Exception('There is more than one \''.$name.'\' in the Twine archive file \''.$this->parserContext->getFilename().'\' with the startnode value \''.$attrs['pid'].'\' in its \'pid\' attribute.');
            }
        } else {
            $textnode->setAccess(false);
        }

        $this->parserContext->setCurrentTextnode($textnode);

        $this->parserContext->setTwineText(true);
    }

    /**
     * @return void
     */
    public function endElement(): void
    {
        $this->textnodeRepository->save($this->parserContext->getCurrentTextnode());

        $nodenameMapping = $this->parserContext->getNodenameMapping();
        $nodenameMapping[$this->twineTextnodeName] = $this->parserContext->getCurrentTextnode()->getId();
        $this->parserContext->setNodenameMapping($nodenameMapping);

        $this->parserContext->setTwineText(false);
    }

    private function createTextnode(string $twineId): Textnode
    {
        $textnode = new Textnode();
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setTopicId($this->parserContext->getImportfile()->getTopicId());
        $textnode->setLicenseeId($this->parserContext->getImportfile()->getLicenseeId());
        $textnode->setImportfileId($this->parserContext->getImportfile()->getId());
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTwineId($twineId);

        return $textnode;
    }

    private function getTwineId(string $tagString, string $textnodeTitle): string
    {
        if (empty($tagString)) {
            throw new \Exception('no ID given for Textnode "'.$textnodeTitle.'"');
        }
        $tagArray = explode(' ', $tagString);

        $twineId = false;

        foreach ($tagArray as $tag) {
            if (0 === strpos($tag, 'ID:')) {
                $twineId = substr($tag, 3);
            }
        }

        if ($twineId === false) {
            throw new \Exception('no ID given for Textnode "'.$textnodeTitle.'"');
        }

        return $twineId;
    }
}
