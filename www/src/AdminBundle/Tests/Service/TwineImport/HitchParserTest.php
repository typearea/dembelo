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

/**
 * @package AdminBundle\Test
 */

namespace AdminBundle\Service\TwineImport;

use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HitchParserTest
 * @package AdminBundle\Service\TwineImport
 */
class HitchParserTest extends WebTestCase
{
    /**
     * @var HitchParser
     */
    private $hitchParser;

    /**
     * @var TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $textnodeRepositoryMock;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->hitchParser = new HitchParser($this->textnodeRepositoryMock);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseDoubleArrowRightWithEmptyLeftPart(): void
    {
        $content = '-->textnodeId';
        $twineName = 'bar';
        $name = 'someName';
        $this->hitchParser->parseDoubleArrowRight($content, $twineName, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseDoubleArrowRightWithEmptyRightPart(): void
    {
        $content = 'description-->';
        $twineName = 'bar';
        $name = 'someName';
        $this->hitchParser->parseDoubleArrowRight($content, $twineName, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseDoubleArrowRightWithNoValidTextnodeIdOnRightPart(): void
    {
        $content = 'description-->invalidTextnodeId';
        $twineName = 'bar';
        $name = 'someName';

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->with('invalidTextnodeId')
            ->willReturn(null);

        $this->hitchParser->parseDoubleArrowRight($content, $twineName, $name);
    }

    /**
     * tests parseDoubleArrowRight with a valid textnode id
     */
    public function testParseDoubleArrowRightWithValidTextnodeIdOnRightPart(): void
    {
        $content = 'description-->textnodeId';
        $twineName = 'bar';
        $name = 'someName';

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->with('textnodeId')
            ->willReturn(new Textnode());

        $result = $this->hitchParser->parseDoubleArrowRight($content, $twineName, $name);

        self::assertInternalType('array', $result);
        self::assertArrayHasKey('description', $result);
        self::assertEquals('description', $result['description']);
        self::assertArrayHasKey('textnodeId', $result);
        self::assertEquals('textnodeId', $result['textnodeId']);
        self::assertArrayHasKey('status', $result);
        self::assertEquals(Textnode::HITCH_STATUS_ACTIVE, $result['status']);
    }

    /**
     * @expectedException \Exception
     */
    public function testSingleArrowRightWithEmptyLeftPart(): void
    {
        $content = '->nodeName';
        $name = 'someName';

        $this->hitchParser->parseSingleArrowRight($content, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testSingleArrowRightWithEmptyRightPart(): void
    {
        $content = 'description->';
        $name = 'someName';

        $this->hitchParser->parseSingleArrowRight($content, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testSingleArrowRightWithInvalidMapName(): void
    {
        $content = 'description->name';
        $name = 'someName';

        $mapping = [
            'invalidKey' => 'textNodeId',
        ];

        $this->hitchParser->setNodeNameMapping($mapping);

        $this->hitchParser->parseSingleArrowRight($content, $name);
    }

    /**
     * tests singleArrowRight with a valid map name
     */
    public function testSingleArrowRightWithValidMapName(): void
    {
        $content = 'description->key';
        $name = 'someName';

        $mapping = [
            'key' => 'textnodeId',
        ];

        $this->hitchParser->setNodeNameMapping($mapping);

        $result = $this->hitchParser->parseSingleArrowRight($content, $name);

        self::assertInternalType('array', $result);
        self::assertArrayHasKey('description', $result);
        self::assertEquals('description', $result['description']);
        self::assertArrayHasKey('textnodeId', $result);
        self::assertEquals('textnodeId', $result['textnodeId']);
        self::assertArrayHasKey('status', $result);
        self::assertEquals(Textnode::HITCH_STATUS_ACTIVE, $result['status']);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseSingleArrowLeftWithEmptyLeftPart(): void
    {
        $content = '<-description';
        $name = 'someName';

        $this->hitchParser->parseSingleArrowLeft($content, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseSingleArrowLeftWithEmptyRightPart(): void
    {
        $content = 'mapKey<-';
        $name = 'someName';

        $this->hitchParser->parseSingleArrowLeft($content, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseSingleArrowLeftWithInvalidKey(): void
    {
        $content = 'mapKey<-description';
        $name = 'someName';
        $keyMap = [
            'invalidKey' => 'textnodeId',
        ];

        $this->hitchParser->setNodeNameMapping($keyMap);

        $this->hitchParser->parseSingleArrowLeft($content, $name);
    }

    /**
     * tests parseSingleArrowLeft with a valid key
     */
    public function testParseSingleArrowLeftWithValidKey(): void
    {
        $content = 'mapKey<-description';
        $name = 'someName';
        $keyMap = [
            'mapKey' => 'textnodeId',
        ];

        $this->hitchParser->setNodeNameMapping($keyMap);

        $result = $this->hitchParser->parseSingleArrowLeft($content, $name);

        self::assertInternalType('array', $result);
        self::assertArrayHasKey('description', $result);
        self::assertEquals('description', $result['description']);
        self::assertArrayHasKey('textnodeId', $result);
        self::assertEquals('textnodeId', $result['textnodeId']);
        self::assertArrayHasKey('status', $result);
        self::assertEquals(Textnode::HITCH_STATUS_ACTIVE, $result['status']);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseSimpleHitchWithEmpyContent(): void
    {
        $content = '';
        $name = 'someName';

        $this->hitchParser->parseSimpleHitch($content, $name);
    }

    /**
     * @expectedException \Exception
     */
    public function testParseSimpleHitchWithInvalidKey(): void
    {
        $content = 'invalidKey';
        $name = 'someName';
        $keyMap = [
            'mapKey' => 'textnodeId',
        ];

        $this->hitchParser->setNodeNameMapping($keyMap);
        $this->hitchParser->parseSimpleHitch($content, $name);
    }

    /**
     * tests parseSimpleHitch with a valid key
     */
    public function testParseSimpleHitchWithValidKey(): void
    {
        $content = 'mapKey';
        $name = 'someName';
        $keyMap = [
            'mapKey' => 'textnodeId',
        ];

        $this->hitchParser->setNodeNameMapping($keyMap);
        $result = $this->hitchParser->parseSimpleHitch($content, $name);

        self::assertInternalType('array', $result);
        self::assertArrayHasKey('description', $result);
        self::assertEquals('mapKey', $result['description']);
        self::assertArrayHasKey('textnodeId', $result);
        self::assertEquals('textnodeId', $result['textnodeId']);
        self::assertArrayHasKey('status', $result);
        self::assertEquals(Textnode::HITCH_STATUS_ACTIVE, $result['status']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TextNodeRepositoryInterface
     */
    private function createTextnodeRepositoryMock(): TextNodeRepositoryInterface
    {
        return $this->createMock(TextNodeRepositoryInterface::class);
    }
}
