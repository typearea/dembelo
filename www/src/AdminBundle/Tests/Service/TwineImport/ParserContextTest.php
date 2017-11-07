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

namespace AdminBundle\Tests\Service\TwineImport;

use AdminBundle\Service\TwineImport\ParserContext;
use DembeloMain\Document\Importfile;
use DembeloMain\Document\Textnode;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserContextTest
 * @package AdminBundle\Tests\Service\TwineImport
 */
class ParserContextTest extends TestCase
{
    /**
     * @return void
     */
    public function testInit(): void
    {
        /* @var $importFileMock \PHPUnit_Framework_MockObject_MockObject|Importfile*/
        $importFileMock = $this->createMock(Importfile::class);
        $importFileMock->expects(self::once())
            ->method('getLicenseeId')
            ->willReturn('someId');
        $importFileMock->expects(self::once())
            ->method('getFilename')
            ->willReturn('someFilename');

        $parserContext = new ParserContext();
        $parserContext->init($importFileMock);
        self::assertSame($importFileMock, $parserContext->getImportfile());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage no licensee available
     */
    public function testInitWithoutLicenseeId(): void
    {
        /* @var $importFileMock \PHPUnit_Framework_MockObject_MockObject|Importfile*/
        $importFileMock = $this->createMock(Importfile::class);
        $importFileMock->expects(self::any())
            ->method('getLicenseeId')
            ->willReturn(null);
        $importFileMock->expects(self::any())
            ->method('getFilename')
            ->willReturn('someFilename');

        $parserContext = new ParserContext();
        $parserContext->init($importFileMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage no filename available
     */
    public function testInitWithoutFilename(): void
    {
        /* @var $importFileMock \PHPUnit_Framework_MockObject_MockObject|Importfile*/
        $importFileMock = $this->createMock(Importfile::class);
        $importFileMock->expects(self::any())
            ->method('getLicenseeId')
            ->willReturn('someId');
        $importFileMock->expects(self::any())
            ->method('getFilename')
            ->willReturn(null);

        $parserContext = new ParserContext();
        $parserContext->init($importFileMock);
    }

    /**
     * @return void
     */
    public function testGetFilename(): void
    {
        /* @var $importFileMock \PHPUnit_Framework_MockObject_MockObject|Importfile*/
        $importFileMock = $this->createMock(Importfile::class);
        $importFileMock->expects(self::any())
            ->method('getLicenseeId')
            ->willReturn('someId');
        $importFileMock->expects(self::any())
            ->method('getFilename')
            ->willReturn('someFilename');

        $parserContext = new ParserContext();
        $parserContext->init($importFileMock);
        self::assertEquals('someFilename', $parserContext->getFilename());
    }

    /**
     * @return void
     */
    public function testIsTwineRelevant(): void
    {
        $parserContext = new ParserContext();
        self::assertFalse($parserContext->isTwineRelevant());
        $parserContext->setTwineRelevant(true);
        self::assertTrue($parserContext->isTwineRelevant());
    }

    /**
     * @return void
     */
    public function testGetCurrentTextnode(): void
    {
        $expectedTextnodeMapping = [
            'someTwineId' => 'someId',
        ];
        /* @var $textnodeMock \PHPUnit_Framework_MockObject_MockObject|Textnode*/
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::once())
            ->method('getTwineId')
            ->willReturn('someTwineId');
        $textnodeMock->expects(self::once())
            ->method('getId')
            ->willReturn('someId');
        $parserContext = new ParserContext();
        self::assertNull($parserContext->getCurrentTextnode());
        $parserContext->setCurrentTextnode($textnodeMock);
        self::assertSame($textnodeMock, $parserContext->getCurrentTextnode());
        self::assertEquals($expectedTextnodeMapping, $parserContext->getTextnodeMapping());
        $parserContext->clearTextnodeMapping();
        self::assertEquals([], $parserContext->getTextnodeMapping());
    }

    /**
     * @return void
     */
    public function testIsTwineText(): void
    {
        $parserContext = new ParserContext();
        self::assertFalse($parserContext->isTwineText());
        $parserContext->setTwineText(true);
        self::assertTrue($parserContext->isTwineText());
    }

    /**
     * @return void
     */
    public function testGetTwineStartnodeId(): void
    {
        $parserContext = new ParserContext();
        self::assertNull($parserContext->getTwineStartnodeId());
        $parserContext->setTwineStartnodeId(13);
        self::assertEquals(13, $parserContext->getTwineStartnodeId());
    }

    /**
     * @return void
     */
    public function testIsAccessSet(): void
    {
        $parserContext = new ParserContext();
        self::assertFalse($parserContext->isAccessSet());
        $parserContext->setAccessSet(true);
        self::assertTrue($parserContext->isAccessSet());
    }
}
