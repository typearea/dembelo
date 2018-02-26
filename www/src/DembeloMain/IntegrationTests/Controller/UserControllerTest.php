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

use DembeloMain\IntegrationTests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group integration
 */
class UserControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testLoginAction(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $response = $client->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode(), $response->getContent());
    }

    /**
     * @return void
     */
    public function testRegistrationAction(): void
    {
        $client = static::createClient();

        $client->request('GET', '/registration');

        $response = $client->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode(), $response->getContent());
    }
}
