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

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use MongoId;
use Hyphenator\Core as Hyphenator;

/**
 * Class TextNodeRepository
 * @method findOneBy(array $where): Textnode
 */
class TextNodeRepository extends AbstractRepository implements TextNodeRepositoryInterface
{
    /**
     * finds textnodes by importfileId
     *
     * @param string $importfileId
     *
     * @return Textnode[]
     */
    public function findByImportfileId($importfileId)
    {
        return $this->findBy(array('importfileId' => new MongoId($importfileId)));
    }

    /**
     * finds a textnode by importfileId and twineId
     * @param Importfile $importfile
     * @param string     $twineId
     *
     * @return Textnode
     */
    public function findByTwineId(Importfile $importfile, $twineId)
    {
        $textnode = $this->findOneBy(
            array(
                'importfileId' => new MongoId($importfile->getId()),
                'twineId'      => $twineId,
            )
        );

        return $textnode;
    }

    /**
     * sets textnodes to status=inactive that are not in $existingTextnodeIds
     * @param Importfile $importfile
     * @param array      $existingTextnodeIds array of textnodeIds
     *
     * @return void
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function disableOrphanedNodes(Importfile $importfile, array $existingTextnodeIds)
    {
        $this->getDocumentManager()->createQueryBuilder(Textnode::class)
            ->updateMany()
            ->field('status')->set(Textnode::STATUS_INACTIVE)
            ->field('importfileId')->equals(new \MongoId($importfile->getId()))
            ->field('id')->notIn($existingTextnodeIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $arbitraryId textnode arbitrary id
     *
     * @return Textnode|null
     */
    public function findOneActiveByArbitraryId($arbitraryId): ?Textnode
    {
        return $this->findOneBy(
            array(
                'arbitraryId' => $arbitraryId,
                'status' => Textnode::STATUS_ACTIVE,
            )
        );
    }

    /**
     * @param string $id Textnode Id
     *
     * @return Textnode
     */
    public function findOneActiveById(string $id): ?Textnode
    {
        return $this->findOneBy(
            array(
                'id' => new \MongoId($id),
                'status' => Textnode::STATUS_ACTIVE,
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @param Textnode $textnode
     *
     * @return
     */
    public function setHyphenatedText(Textnode $textnode): void
    {
        $hyphenator = new Hyphenator();
        $hyphenator->registerPatterns('de');
        $hyphenator->setHyphen('&shy;');

        $textnode->setTextHyphenated($hyphenator->hyphenate($textnode->getText()));
    }

    /**
     * @inheritdoc
     *
     * @param string $topicId
     *
     * @return Textnode|null
     */
    public function getTextnodeToRead($topicId): ?Textnode
    {
        return $this->createQueryBuilder()
            ->field('topicId')->equals(new \MongoId($topicId))
            ->field('status')->equals(Textnode::STATUS_ACTIVE)
            ->field('access')->equals(true)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param Textnode $object
     *
     * @return void
     */
    protected function beforeSave($object)
    {
        parent::beforeSave($object);
        if (null === $object->getArbitraryId()) {
            $object->setArbitraryId($this->createArbitraryId($object));
        }
    }

    /**
     * @param Textnode $object
     *
     * @return string
     */
    private function createArbitraryId(Textnode $object): string
    {
        $id = substr(md5(time().$object->getTwineId().substr($object->getText(), 0, 100)), 0, 15);
        $exists = count($this->findBy(array('arbitraryId' => $id))) > 0;

        if ($exists) {
            return $this->createArbitraryId($object);
        }

        return $id;
    }
}
