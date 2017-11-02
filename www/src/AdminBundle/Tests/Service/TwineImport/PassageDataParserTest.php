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
use PHPUnit\Framework\TestCase;

/**
 * Class PassageDataParserTest
 * @package AdminBundle\Service\TwineImport
 */
class PassageDataParserTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepositoryInterface
     */
    private $textnodeRepositoryMock;

    /**
     * @var PassageDataParser
     */
    private $parser;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->textnodeRepositoryMock = $this->createMock(TextNodeRepositoryInterface::class);
        $this->parser = new PassageDataParser($this->textnodeRepositoryMock);
    }

    /**
     * @return void
     */
    public function testStartElementForNewNonAccessTextnode(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => 1,
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];
        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $parserContext->expects(self::once())
            ->method('setTwineText')
            ->with(true);
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn([]);
        $parserContext->expects(self::any())
            ->method('getTwineStartnodeId')
            ->willReturn(-1);
        $parserContext->expects(self::never())
            ->method('setAccessSet');
        $parserContext->expects(self::any())
            ->method('setCurrentTextnode')
            ->willReturnCallback(function (Textnode $textnode) {
                self::assertEquals('someId', $textnode->getTwineId());
                self::assertFalse($textnode->getAccess());
                self::assertEquals('someName', $textnode->getMetadata()['Titel']);
            });

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findByTwineId')
            ->willReturn(null);

        $this->parser->setParserContext($parserContext);
        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     */
    public function testStartElementForOldNonAccessTextnode(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => 1,
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        /* @var $textnodeMock \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::once())
            ->method('setAccess')
            ->with(false);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $parserContext->expects(self::once())
            ->method('setTwineText')
            ->with(true);
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn([]);
        $parserContext->expects(self::any())
            ->method('getTwineStartnodeId')
            ->willReturn(-1);
        $parserContext->expects(self::never())
            ->method('setAccessSet');
        $parserContext->expects(self::any())
            ->method('setCurrentTextnode')
            ->willReturnCallback(function (Textnode $textnode) use ($textnodeMock) {
                self::assertSame($textnodeMock, $textnode);
            });

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findByTwineId')
            ->willReturn($textnodeMock);

        $this->parser->setParserContext($parserContext);
        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     */
    public function testStartElementForOldAccessTextnode(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => 1,
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        /* @var $textnodeMock \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::once())
            ->method('setAccess')
            ->with(true);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $parserContext->expects(self::once())
            ->method('setTwineText')
            ->with(true);
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn([]);
        $parserContext->expects(self::any())
            ->method('getTwineStartnodeId')
            ->willReturn(1);
        $parserContext->expects(self::once())
            ->method('setAccessSet')
            ->with(true);
        $parserContext->expects(self::any())
            ->method('setCurrentTextnode')
            ->willReturnCallback(function (Textnode $textnode) use ($textnodeMock) {
                self::assertSame($textnodeMock, $textnode);
            });

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findByTwineId')
            ->willReturn($textnodeMock);

        $this->parser->setParserContext($parserContext);
        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage Nested 'someTagName' found in Twine archive file ''
     */
    public function testStartElementForNestedPassageDatas(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => 1,
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(true);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'someTagName' in the Twine archive file '' which is missing its 'pid' attribute.
     */
    public function testStartElementForMissingPIDAttribute(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'someTagName' in the Twine archive file '' which hasn't a numeric value in its 'pid' attribute ('someNonNumericPid' was found instead).
     */
    public function testStartElementForNonNumericPIDAttribute(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => 'someNonNumericPid',
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage There is a 'someTagName' in the Twine archive file '' which has a non unique 'id' tag [someId], in node 'someName'
     */
    public function testStartElementForNonUniquePIDAttribute(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => '2',
            'tags' => 'ID:someId',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $parserContext->expects(self::once())
            ->method('getTextnodeMapping')
            ->willReturn(['someId' => 'someTextnodeId']);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage no ID given for Textnode "someName"
     */
    public function testStartElementForMissingTwineIdTag(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => '2',
            'tags' => '',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage no ID given for Textnode "someName"
     */
    public function testStartElementForInvalidTwineIdTag(): void
    {
        $elementName = 'someTagName';
        $attributes = [
            'pid' => '2',
            'tags' => 'nonempty',
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineText')
            ->willReturn(false);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($elementName, $attributes);
    }

    /**
     * @return void
     */
    public function testEndElement(): void
    {
        $textnodeMock = $this->createMock(Textnode::class);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('getCurrentTextnode')
            ->willReturn($textnodeMock);
        $parserContext->expects(self::once())
            ->method('setTwineText')
            ->with(false);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('save')
            ->with($textnodeMock);

        $this->parser->endElement();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ParserContext
     */
    private function createParserContextMock(): ParserContext
    {
        return $this->createMock(ParserContext::class);
    }
}
