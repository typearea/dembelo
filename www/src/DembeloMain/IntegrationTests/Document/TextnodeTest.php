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

namespace DembeloMain\IntegrationTests\Document;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use DembeloMain\IntegrationTests\WebTestCase;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * @group integration
 */
class TextnodeTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testChildHitchesIsEmptyForNewTextnode(): void
    {
        $textnode = new Textnode();
        $textnode->setText('firstTextnode');
        $this->getMongo()->persist($textnode);
        $this->getMongo()->flush();
        $this->getMongo()->clear();
        $textnodes = $this->getMongo()->getRepository(Textnode::class)->findAll();

        self::assertEquals('firstTextnode', $textnodes[0]->getText());
        self::assertInstanceOf(PersistentCollection::class, $textnodes[0]->getChildHitches());
        self::assertEquals(0, $textnodes[0]->getChildHitches()->count());
    }

    /**
     * @return void
     */
    public function testChildHitchesReference(): void
    {
        $textnode = new Textnode();
        $this->getMongo()->persist($textnode);
        $hitch = new TextnodeHitch();
        $this->getMongo()->persist($hitch);
        $hitch->setSourceTextnode($textnode);

        $this->getMongo()->flush();
        $this->getMongo()->clear();

        $textnodes = $this->getMongo()->getRepository(Textnode::class)->findAll();
        $hitches = $this->getMongo()->getRepository(TextnodeHitch::class)->findAll();

        self::assertInstanceOf(Textnode::class, $textnodes[0]);
        self::assertInstanceOf(TextnodeHitch::class, $hitches[0]);
        self::assertSame($textnodes[0], $hitches[0]->getSourceTextnode());
        self::assertEquals(1, $textnodes[0]->getChildHitches()->count());
        self::assertSame($hitches[0], $textnodes[0]->getChildHitches()->first());
    }
}
