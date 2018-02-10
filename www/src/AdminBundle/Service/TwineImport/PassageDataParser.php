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
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class PassageDataParser
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
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * PassageDataParser constructor.
     * @param TextNodeRepositoryInterface $textNodeRepository
     * @param DocumentManager             $documentManager
     */
    public function __construct(TextNodeRepositoryInterface $textNodeRepository, DocumentManager $documentManager)
    {
        $this->textnodeRepository = $textNodeRepository;
        $this->documentManager = $documentManager;
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
     * @param array  $attrs
     *
     * @throws \Exception
     */
    public function startElement(string $name, array $attrs): void
    {
        if ($this->parserContext->isTwineText()) {
            throw new \Exception(sprintf("Nested '%s' found in Twine archive file '%s'.", $name, $this->parserContext->getFilename()));
        }

        if (isset($attrs['pid']) !== true) {
            throw new \Exception(sprintf("There is a '%s' in the Twine archive file '%s' which is missing its 'pid' attribute.", $name, $this->parserContext->getFilename()));
        }

        if (is_numeric($attrs['pid']) !== true) {
            throw new \Exception(sprintf("There is a '%s' in the Twine archive file '%s' which hasn't a numeric value in its 'pid' attribute ('%s' was found instead).", $name, $this->parserContext->getFilename(), $attrs['pid']));
        }

        $twineId = $this->getTwineId($attrs['tags'], $attrs['name']);

        if (array_key_exists($twineId, $this->parserContext->getTextnodeMapping()) === true) {
            throw new \Exception(sprintf("There is a '%s' in the Twine archive file '%s' which has a non unique 'id' tag [%s], in node '%s'", $name, $this->parserContext->getFilename(), $twineId, $attrs['name']));
        }

        $textnode = $this->textnodeRepository->findByTwineId($this->parserContext->getImportfile(), $twineId);

        if (null === $textnode) {
            $textnode = $this->createTextnode($twineId);
        } else {
            $textnode->setText('');
            // @todo clear hitches
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
                throw new \Exception(sprintf('There is more than one \'%s\' in the Twine archive file \'%s\' with the startnode value \'%s\' in its \'pid\' attribute.', $name, $this->parserContext->getFilename(), $attrs['pid']));
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
        $nodenameMapping = $this->parserContext->getNodenameMapping();
        $nodenameMapping[$this->twineTextnodeName] = $this->parserContext->getCurrentTextnode();
        $this->parserContext->setNodenameMapping($nodenameMapping);

        $this->parserContext->setTwineText(false);
    }

    /**
     * @param string $twineId
     *
     * @return Textnode
     */
    private function createTextnode(string $twineId): Textnode
    {
        $textnode = new Textnode();
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setTopicId($this->parserContext->getImportfile()->getTopicId());
        $textnode->setLicenseeId($this->parserContext->getImportfile()->getLicenseeId());
        $textnode->setImportfileId($this->parserContext->getImportfile()->getId());
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTwineId($twineId);

        $this->textnodeRepository->decorateArbitraryId($textnode);

        $this->documentManager->persist($textnode);

        return $textnode;
    }

    /**
     * @param string $tagString
     * @param string $textnodeTitle
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getTwineId(string $tagString, string $textnodeTitle): string
    {
        if (empty($tagString)) {
            throw new \Exception(sprintf('no ID given for Textnode "%s"', $textnodeTitle));
        }
        $tagArray = explode(' ', $tagString);

        $twineId = false;

        foreach ($tagArray as $tag) {
            if (0 === strpos($tag, 'ID:')) {
                $twineId = substr($tag, 3);
            }
        }

        if (false === $twineId) {
            throw new \Exception(sprintf('no ID given for Textnode "%s"', $textnodeTitle));
        }

        return $twineId;
    }
}
