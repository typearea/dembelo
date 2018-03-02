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

namespace DembeloMain\IntegrationTests\Controller;

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Document\Topic;
use DembeloMain\IntegrationTests\WebTestCase;

/**
 * @group integration
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testReadTopicActionWithAccessNode(): void
    {
        $client = static::createClient();

        $topic = new Topic();
        $topic->setStatus(Topic::STATUS_ACTIVE);
        $this->getMongo()->persist($topic);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($topic->getId());
        $textnode->setAccess(true);
        $textnode->setArbitraryId('someArbitraryId');
        $this->getMongo()->persist($textnode);
        $this->getMongo()->flush();

        $client = static::createClient();

        $client->request('GET', '/themenfeld/'.$topic->getId());

        $response = $client->getResponse();

        self::assertNotNull($response);
        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->isRedirect('/collect/someArbitraryId'), $response->getContent());
    }

    /**
     * @return void
     */
    public function testReadTextnodeActionWithoutHitchRedirectsToFinanceNodeAction(): void
    {
        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setAccess(true);
        $textnode->setArbitraryId('someArbitraryId');
        $this->getMongo()->persist($textnode);
        $this->getMongo()->flush();

        $client = static::createClient();
        $client->request('GET', '/text/someArbitraryId');
        $response = $client->getResponse();

        self::assertNotNull($response);
        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->isRedirect('/collect/someArbitraryId'));
    }

    /**
     * @return void
     */
    public function testReadTextnodeActionWithHitchShowsTextnode(): void
    {
        $topic = new Topic();
        $topic->setStatus(Topic::STATUS_ACTIVE);
        $topic->setName('someTopic');
        $this->getMongo()->persist($topic);
        $this->getMongo()->flush();

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setAccess(true);
        $textnode->setArbitraryId('someArbitraryId');
        $textnode->setTopicId($topic->getId());
        $textnode->setMetadata(
            [
                'Titel' => 'some title',
                'Autor' => 'some author',
                'Verlag' => 'some publisher',
            ]
        );
        $this->getMongo()->persist($textnode);

        $textnodeChild = new Textnode();
        $textnodeChild->setStatus(Textnode::STATUS_ACTIVE);
        $textnodeChild->setAccess(true);
        $textnodeChild->setArbitraryId('someArbitraryIdChild');
        $textnodeChild->setTopicId($topic->getId());
        $this->getMongo()->persist($textnodeChild);

        $hitch = new TextnodeHitch();
        $hitch->setSourceTextnode($textnode);
        $hitch->setTargetTextnode($textnodeChild);
        $hitch->setDescription('hitch to target textnode');
        $hitch->setStatus(TextnodeHitch::STATUS_ACTIVE);
        $this->getMongo()->persist($hitch);

        $this->getMongo()->flush();

        $client = static::createClient();
        $client->request('GET', '/text/someArbitraryId');
        $response = $client->getResponse();

        self::assertNotNull($response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertContains(
            'hitch to target textnode',
            $response->getContent()
        );
    }
}
