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

    public function testId()
    {
        $this->assertNull($this->importfile->getId());
        $this->importfile->setId('someId');
        $this->assertEquals('someId', $this->importfile->getId());
    }

    public function testName()
    {
        $this->assertNull($this->importfile->getName());
        $this->importfile->setName('someName');
        $this->assertEquals('someName', $this->importfile->getName());
    }

    public function testLicenseeId()
    {
        $this->assertNull($this->importfile->getLicenseeId());
        $this->importfile->setLicenseeId('someLicenseeId');
        $this->assertEquals('someLicenseeId', $this->importfile->getLicenseeId());
    }

    public function testImported()
    {
        $this->assertNull($this->importfile->getImported());
        $this->importfile->setImported('someImported');
        $this->assertEquals('someImported', $this->importfile->getImported());
    }

    public function testAuthor()
    {
        $this->assertNull($this->importfile->getAuthor());
        $this->importfile->setAuthor('someAuthor');
        $this->assertEquals('someAuthor', $this->importfile->getAuthor());
    }

    public function testPublisher()
    {
        $this->assertNull($this->importfile->getPublisher());
        $this->importfile->setPublisher('somePublisher');
        $this->assertEquals('somePublisher', $this->importfile->getPublisher());
    }

    public function testOrgname()
    {
        $this->assertNull($this->importfile->getOrgname());
        $this->importfile->setOrgname('someOrgname');
        $this->assertEquals('someOrgname', $this->importfile->getOrgname());
    }

    public function testFilename()
    {
        $this->assertNull($this->importfile->getFilename());
        $this->importfile->setFilename('someFilename');
        $this->assertEquals('someFilename', $this->importfile->getFilename());
    }
}