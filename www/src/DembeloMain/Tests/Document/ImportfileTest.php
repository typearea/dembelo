<?php
/* Copyright (C) 2016 Michael Giesler
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
/**
 * @package DembeloMain
 */

namespace DembeloMain\Tests\Document;

use DembeloMain\Document\Importfile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DocumentTextnodeTest
 */
class ImportfileTest extends WebTestCase
{
    /**
     * @var Importfile
     */
    private $importfile;

    /**
     * setUp method
     */
    public function setUp()
    {
        $this->importfile = new Importfile();
    }

    /**
     * tests (get|set)Id
     */
    public function testId()
    {
        $this->assertNull($this->importfile->getId());
        $this->importfile->setId('someId');
        $this->assertEquals('someId', $this->importfile->getId());
    }

    /**
     * tests (get|set)Name
     */
    public function testName()
    {
        $this->assertNull($this->importfile->getName());
        $this->importfile->setName('someName');
        $this->assertEquals('someName', $this->importfile->getName());
    }

    /**
     * tests (get|set)LicenseeId
     */
    public function testLicenseeId()
    {
        $this->assertNull($this->importfile->getLicenseeId());
        $this->importfile->setLicenseeId('someLicenseeId');
        $this->assertEquals('someLicenseeId', $this->importfile->getLicenseeId());
    }

    /**
     * tests (get|set)Imported
     */
    public function testImported()
    {
        $this->assertNull($this->importfile->getImported());
        $this->importfile->setImported(2);
        $this->assertEquals(2, $this->importfile->getImported());
    }

    /**
     * tests (get|set)Author
     */
    public function testAuthor()
    {
        $this->assertNull($this->importfile->getAuthor());
        $this->importfile->setAuthor('someAuthor');
        $this->assertEquals('someAuthor', $this->importfile->getAuthor());
    }

    /**
     * tests (get|set)Publisher
     */
    public function testPublisher()
    {
        $this->assertNull($this->importfile->getPublisher());
        $this->importfile->setPublisher('somePublisher');
        $this->assertEquals('somePublisher', $this->importfile->getPublisher());
    }

    /**
     * tests (get|set)Orgname
     */
    public function testOrgname()
    {
        $this->assertNull($this->importfile->getOriginalname());
        $this->importfile->setOriginalname('someOrgname');
        $this->assertEquals('someOrgname', $this->importfile->getOriginalname());
    }

    /**
     * tests (get|set)Filename
     */
    public function testFilename()
    {
        $this->assertNull($this->importfile->getFilename());
        $this->importfile->setFilename('someFilename');
        $this->assertEquals('someFilename', $this->importfile->getFilename());
    }
}
