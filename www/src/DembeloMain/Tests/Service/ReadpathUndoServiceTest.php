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
declare(strict_types=1);

namespace DembeloMain\Tests\Service;

use DembeloMain\Service\ReadpathUndoService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class ReadpathUndoServiceTest
 * @package DembeloMain\Tests\Service
 */
class ReadpathUndoServiceTest extends WebTestCase
{
    /**
     * @var ReadpathUndoService
     */
    private $undoStack;

    private $session;

    public function setUp(): void
    {
        parent::setUp();
        $this->session = new Session(new MockArraySessionStorage());
        $this->undoStack = new ReadpathUndoService($this->session);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->undoStack = null;
    }

    public function testAddAnItemWillReturnThisAsCurrentItem(): void
    {
        $item = 'someItem';
        $this->undoStack->add($item);
        self::assertEquals($item, $this->undoStack->getCurrentItem());
    }

    /**
     * @expectedException \TypeError
     */
    public function testAddAnIntegerWillThrowTypeError(): void
    {
        $this->undoStack->add(123);
    }

    public function testGetCurrentWithNoCurrentItem(): void
    {
        self::assertNull($this->undoStack->getCurrentItem());
    }

    public function testGetCurrent(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
    }

    public function testUndo(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        $this->undoStack->undo();
        self::assertEquals('first', $this->undoStack->getCurrentItem());
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testUndoThrowsExceptionWhenNothingCanBeUndone(): void
    {
        $this->undoStack->undo();
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testUndoThrowsExceptionWhenPastStackIsStillEmpty(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->undo();
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testRedoThrowsExceptionWhenNothingCanBeRedone(): void
    {
        $this->undoStack->redo();
    }

    public function testRedo(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        $this->undoStack->undo();
        $this->undoStack->redo();
        self::assertEquals('second', $this->undoStack->getCurrentItem());

    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testAddRemovingFutureValues(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->undo();
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('third');
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        $this->undoStack->redo();

    }

    public function testAComplexSequence(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->add('third');
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        $this->undoStack->undo();
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->undo();
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->redo();
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->redo();
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        $this->undoStack->undo();
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->add('fourth');
        self::assertEquals('fourth', $this->undoStack->getCurrentItem());
    }

    public function testAddFromSession(): void
    {
        $this->undoStack->add('first');
        $undoStack = new ReadpathUndoService($this->session);
        self::assertEquals('first', $undoStack->getCurrentItem());
    }
}
