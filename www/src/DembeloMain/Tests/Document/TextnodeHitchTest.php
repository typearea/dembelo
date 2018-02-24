<?php
/* Copyright (C) 2018 Michael Giesler
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

namespace DembeloMain\Tests\Document;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use PHPUnit\Framework\TestCase;

/**
 * Class TextnodeHitchTest
 * @package DembeloMain\Tests\Document
 */
class TextnodeHitchTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetId(): void
    {
        $hitch = new TextnodeHitch();
        self::assertNull($hitch->getId());
        $hitch->setId('someId');
        self::assertEquals('someId', $hitch->getId());
    }

    /**
     * @return void
     */
    public function testGetDescription(): void
    {
        $hitch = new TextnodeHitch();
        self::assertNull($hitch->getDescription());
        $hitch->setDescription('someDescription');
        self::assertEquals('someDescription', $hitch->getDescription());
    }

    /**
     * @return void
     */
    public function testGetTargetTextnode(): void
    {
        /** @var Textnode|\PHPUnit_Framework_MockObject_MockObject $textnodeMock */
        $textnodeMock = $this->createMock(Textnode::class);
        $hitch = new TextnodeHitch();
        self::assertNull($hitch->getTargetTextnode());
        $hitch->setTargetTextnode($textnodeMock);
        self::assertSame($textnodeMock, $hitch->getTargetTextnode());
    }

    /**
     * @return void
     */
    public function testGetSourceTextnode(): void
    {
        /** @var Textnode|\PHPUnit_Framework_MockObject_MockObject $textnodeMock */
        $textnodeMock = $this->createMock(Textnode::class);
        $hitch = new TextnodeHitch();
        self::assertNull($hitch->getSourceTextnode());
        $hitch->setSourceTextnode($textnodeMock);
        self::assertSame($textnodeMock, $hitch->getSourceTextnode());
    }
}