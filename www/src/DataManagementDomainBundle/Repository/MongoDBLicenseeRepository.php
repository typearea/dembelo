<?php
/* Copyright (C) 2015 Stephan Kreutzer
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


namespace DataManagementDomainBundle\Repository;

use DataManagementDomain\Licensee\Licensee;
use DataManagementDomain\Licensee\LicenseeId;
use DataManagementDomain\Licensee\LicenseeRepositoryInterface;

/**
 * MongoDBLicenseeRepository
 */
class MongoDBLicenseeRepository implements LicenseeRepositoryInterface
{
    private $mongo = null;
    private $dm = null;
    private $repositoryLicensee;

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $kernel;

        $this->mongo = $kernel->getContainer()->get('doctrine_mongodb');
        $this->dm = $this->mongo->getManager();
        $this->repositoryLicensee = $this->mongo->getRepository('DembeloMain:Licensee');
    }

    /**
     * Finds the first licensee by name.
     * @param string $name Name of the licensee.
     * @return array|null
     */
    public function findByName($name)
    {
        return $this->repositoryLicensee->createQueryBuilder()
            ->field('name')->equals($name)
            ->getQuery()->getSingleResult();
    }
}
