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
declare(strict_types = 1);

namespace DembeloMain\Tests\Service;

use DembeloMain\Service\ReadpathUndoService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class ReadpathUndoServiceTest
 */
class ReadpathUndoServiceTest extends WebTestCase
{
    /**
     * @var ReadpathUndoService
     */
    private $undoStack;

    private $session;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->session = new Session(new MockArraySessionStorage());
        $this->undoStack = new ReadpathUndoService($this->session);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->undoStack = null;
    }

    /**
     * tests add() to return same value with getCurrentItem()
     */
    public function testAddAnItemWillReturnThisAsCurrentItem(): void
    {
        $item = 'someItem';
        $this->undoStack->add($item);
        self::assertEquals($item, $this->undoStack->getCurrentItem());
    }

    /**
     * tests add() throwing a typeError
     * @expectedException \TypeError
     */
    public function testAddAnIntegerWillThrowTypeError(): void
    {
        $this->undoStack->add(123);
    }

    /**
     * tests getCurrentItem with no current item
     */
    public function testGetCurrentWithNoCurrentItem(): void
    {
        self::assertNull($this->undoStack->getCurrentItem());
    }

    /**
     * tests get current
     */
    public function testGetCurrent(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
    }

    /**
     * tests undo
     */
    public function testUndo(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('first', $this->undoStack->getCurrentItem());
    }

    /**
     * tests undo() to return false when nothing can be undone
     */
    public function testUndoReturnsFalseWhenNothingCanBeUndone(): void
    {
        self::assertFalse($this->undoStack->undo());
    }

    /**
     * tests undo() to return false when past stack is still empty
     */
    public function testUndoReturnsFalseWhenPastStackIsStillEmpty(): void
    {
        $this->undoStack->add('first');
        self::assertFalse($this->undoStack->undo());
    }

    /**
     * tests redo() to return false when nothing can be undone
     */
    public function testRedoReturnFalseWhenNothingCanBeRedone(): void
    {
        self::assertFalse($this->undoStack->redo());
    }

    /**
     * tests redo()
     */
    public function testRedo(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        self::assertTrue($this->undoStack->undo());
        self::assertTrue($this->undoStack->redo());
        self::assertEquals('second', $this->undoStack->getCurrentItem());
    }

    /**
     * tests add(), removing future values
     */
    public function testAddRemovingFutureValues(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('third');
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        self::assertFalse($this->undoStack->redo());
    }

    /**
     * tests the ignoring of add() after an undo
     */
    public function testIgnoreAddAfterUndo(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        $this->undoStack->add('third');
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('first', $this->undoStack->getCurrentItem());
    }

    /**
     * tests duplicate entries avoiding
     */
    public function testAvoidDuplicateEntries(): void
    {
        $this->undoStack->add('first');
        $this->undoStack->add('second');
        $this->undoStack->add('second');
        self::assertTrue($this->undoStack->undo());
        self::assertSame('first', $this->undoStack->getCurrentItem());
    }

    /**
     * tests a complex sequence
     */
    public function testAComplexSequence(): void
    {
        $this->undoStack->add('first');
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        $this->undoStack->add('second');
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->add('third');
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('first', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->redo());
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->redo());
        self::assertEquals('third', $this->undoStack->getCurrentItem());
        self::assertTrue($this->undoStack->undo());
        self::assertEquals('second', $this->undoStack->getCurrentItem());
        $this->undoStack->add('fourth');
        self::assertEquals('fourth', $this->undoStack->getCurrentItem());
    }

    /**
     * tests add() from session
     */
    public function testAddFromSession(): void
    {
        $this->undoStack->add('first');
        $undoStack = new ReadpathUndoService($this->session);
        self::assertEquals('first', $undoStack->getCurrentItem());
    }
}
