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
 * Class StoryDataParserTest
 */
class StoryDataParserTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HitchParser
     */
    private $hitchParserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepositoryInterface
     */
    private $textnodeRepositoryMock;

    /**
     * @var StoryDataParser
     */
    private $parser;

    /**
     * @var \Parsedown
     */
    private $parsedownMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->hitchParserMock = $this->createMock(HitchParser::class);
        $this->textnodeRepositoryMock = $this->createMock(TextNodeRepositoryInterface::class);
        $this->parsedownMock = $this->createMock(\Parsedown::class);
        $this->parsedownMock->method('parse')->willReturnArgument(0);
        $this->parser = new StoryDataParser($this->hitchParserMock, $this->textnodeRepositoryMock, $this->parsedownMock);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage Nested 'storyPassage' found in Twine archive file 'someFilename'.
     */
    public function testStartElementForNestedStoryData(): void
    {
        $tagName = 'storyPassage';
        $attributes = [];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineRelevant')
            ->willReturn(true);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($tagName, $attributes);
    }

    /**
     * @return void
     */
    public function testStartElementForMissingStartnodeAttribute(): void
    {
        $tagName = 'storyPassage';
        $attributes = [];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineRelevant')
            ->willReturn(false);
        $parserContext->expects(self::never())
            ->method('setTwineStartnodeId');
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($tagName, $attributes);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage There is a 'storyPassage' in the Twine archive file 'someFilename' which is missing its 'name' attribute.
     */
    public function testStartElementForMissingNameAttribute(): void
    {
        $tagName = 'storyPassage';
        $attributes = [
            'startnode' => 1,
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineRelevant')
            ->willReturn(false);
        $parserContext->expects(self::never())
            ->method('setTwineStartnodeId');
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($tagName, $attributes);
    }

    /**
     * @return void
     */
    public function testStartElement(): void
    {
        $tagName = 'storyPassage';
        $attributes = [
            'startnode' => 1,
            'name' => 'someName',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('isTwineRelevant')
            ->willReturn(false);
        $parserContext->expects(self::once())
            ->method('setTwineStartnodeId')
            ->with(1);
        $parserContext->expects(self::once())
            ->method('clearTextnodeMapping');
        $parserContext->expects(self::once())
            ->method('setTwineRelevant')
            ->with(true);
        $this->parser->setParserContext($parserContext);

        $this->parser->startElement($tagName, $attributes);
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage The Dembelo Textnode with Id 'someTextnodeId' doesn't exist, but should by now.
     */
    public function testEndElementForNotExistingTextnodeId(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::never())
            ->method('setAccessSet')
            ->with(false);
        $parserContext->expects(self::once())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     */
    public function testEndElementWithoutAnyHitches(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText'."\n"."someOtherText ");
        $textnodeMock->expects(self::once())
            ->method('setText')
            ->willReturnCallback(function (string $textNew) {
                self::assertEquals('<p>someText</p><p>someOtherText</p>', $textNew);
            });

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::once())
            ->method('setAccessSet')
            ->with(false);
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->hitchParserMock->expects(self::never())
            ->method('parseDoubleArrowRight');
        $this->hitchParserMock->expects(self::never())
            ->method('parseSingleArrowRight');
        $this->hitchParserMock->expects(self::never())
            ->method('parseSingleArrowLeft');
        $this->hitchParserMock->expects(self::never())
            ->method('parseSimpleHitch');

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     */
    public function testEndElementWithHitches(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        $someHitch = [];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText [[foo1-->foo2]] [[foo3->foo4]] [[foo5<-foo6]] [[foo7]] [[foo8>:<foo9]]');
        $textnodeMock->expects(self::once())
            ->method('setText')
            ->willReturnCallback(function (string $textNew) {
                self::assertEquals('<p>someText</p>', $textNew);
            });
        $textnodeMock->expects(self::exactly(4))
            ->method('appendHitch')
            ->willReturn(true);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->hitchParserMock->expects(self::once())
            ->method('parseDoubleArrowRight')
            ->with('foo1-->foo2')
            ->willReturn($someHitch);
        $this->hitchParserMock->expects(self::once())
            ->method('parseSingleArrowRight')
            ->with('foo3->foo4')
            ->willReturn($someHitch);
        $this->hitchParserMock->expects(self::once())
            ->method('parseSingleArrowLeft')
            ->with('foo5<-foo6')
            ->willReturn($someHitch);
        $this->hitchParserMock->expects(self::once())
            ->method('parseSimpleHitch')
            ->with('foo7')
            ->willReturn($someHitch);

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage There is a textnode in the Twine archive file which has more than 8 links.
     */
    public function testEndElementExceedingMaximumHitchCount(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        $someHitch = [];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText [[foo1-->foo2]] ');
        $textnodeMock->expects(self::once())
            ->method('getHitchCount')
            ->willReturn(10);
        $textnodeMock->expects(self::never())
            ->method('appendHitch');

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->hitchParserMock->expects(self::once())
            ->method('parseDoubleArrowRight')
            ->with('foo1-->foo2')
            ->willReturn($someHitch);

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage Failed to append hitch for a textnode
     */
    public function testEndElementForFailingAppendHitch(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        $someHitch = [];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText [[foo1-->foo2]] ');
        $textnodeMock->expects(self::once())
            ->method('appendHitch')
            ->willReturn(false);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->hitchParserMock->expects(self::once())
            ->method('parseDoubleArrowRight')
            ->with('foo1-->foo2')
            ->willReturn($someHitch);

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage The Twine archive file contains a 'someName' with the invalid element '[[>:<value]]'.
     */
    public function testEndElementForInvalidMetadataField(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        $someHitch = [];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText [[>:<value]] ');

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->parser->endElement('someName');
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     *
     * @expectedExceptionMessage There is a textnode in the Twine archive file which contains the metadata field 'key' twice or would overwrite the already existing value of that field.
     */
    public function testEndElementForAlreadyExistingMetadata(): void
    {
        $textnodeMapping = [
            'someTwineId' => 'someTextnodeId',
        ];

        /* @var $textnode \PHPUnit_Framework_MockObject_MockObject|Textnode */
        $textnodeMock = $this->createMock(Textnode::class);
        $textnodeMock->expects(self::any())
            ->method('getText')
            ->willReturn('someText [[key>:<value]] ');
        $textnodeMock->expects(self::once())
            ->method('getMetadata')
            ->willReturn(['key' => 'foobar']);

        $parserContext = $this->createParserContextMock();
        $parserContext->expects(self::any())
            ->method('getTextnodeMapping')
            ->willReturn($textnodeMapping);
        $this->parser->setParserContext($parserContext);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->willReturn($textnodeMock);

        $this->parser->endElement('someName');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ParserContext
     */
    private function createParserContextMock(): ParserContext
    {
        $mock = $this->createMock(ParserContext::class);
        $mock->expects(self::any())
            ->method('getFilename')
            ->willReturn('someFilename');

        return $mock;
    }
}
