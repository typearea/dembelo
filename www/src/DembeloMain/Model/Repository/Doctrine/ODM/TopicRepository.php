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

use DembeloMain\Document\Topic;
use DembeloMain\Model\Repository\TopicRepositoryInterface;

/**
 * Class TopicRepository
 */
class TopicRepository extends AbstractRepository implements TopicRepositoryInterface
{
    /**
     * Find all active topics
     * @return Topic[]
     */
    public function findByStatusActive()
    {
        return $this->findBy(array('status' => Topic::STATUS_ACTIVE), array('sortKey' => 'ASC'));
    }

    /**
     * finds filtered topics
     * @param array $filters
     * @param array $orderBy
     *
     * @return mixed
     */
    public function findFiltered(array $filters = array(), array $orderBy = array())
    {
        $query = $this->createQueryBuilder();

        foreach ($filters as $field => $value) {
            if (empty($value) && '0' !== $value) {
                continue;
            }
            if ('status' === $field) {
                //$value = $value === 'aktiv' ? 1 : 0;
                $query->field($field)->equals((int) $value);
            } else {
                $query->field($field)->equals(new \MongoRegex('/.*'.$value.'.*/i'));
            }
        }

        if (count($orderBy) === 2) {
            $query->sort($orderBy[0], $orderBy[1]);
        }

        return $query->getQuery()->execute();
    }

    /**
     * @param string $name
     *
     * @return Topic|null
     */
    public function findOneByName(string $name): ?Topic
    {
        return $this->findOneBy(['name' => $name]);
    }
}
