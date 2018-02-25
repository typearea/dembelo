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

namespace DembeloMain\Tests\EventListener;

use DembeloMain\EventListener\RequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class RequestListenerTest
 */
class RequestListenerTest extends TestCase
{
    /**
     * @return void
     */
    public function testOnRequestForNonAjaxCall(): void
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(GetResponseEvent::class);
        $eventMock->expects(self::never())->method('setResponse');

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $eventMock->method('getRequest')->willReturn($requestMock);
        $requestMock->method('isXmlHttpRequest')->willReturn(false);

        $listener = new RequestListener();
        $listener->onRequest($eventMock);
    }

    /**
     * @return void
     */
    public function testOnRequestForAjaxCallWithOtherRouteThanLogin(): void
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(GetResponseEvent::class);
        $eventMock->expects(self::never())->method('setResponse');

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $eventMock->method('getRequest')->willReturn($requestMock);
        $requestMock->method('isXmlHttpRequest')->willReturn(true);
        $requestMock->method('getUri')->willReturn('someUro');

        $listener = new RequestListener();
        $listener->onRequest($eventMock);
    }

    /**
     * @return void
     */
    public function testOnRequestForAjaxCallWithLoginRouteOverwriteResponse(): void
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(GetResponseEvent::class);
        $eventMock->expects(self::once())
            ->method('setResponse')
            ->willReturnCallback(
                function (JsonResponse $response) {
                    self::assertEquals('{"session_expired":true}', $response->getContent());
                }
            );

        /** @var RequestStack|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->createMock(Request::class);
        $eventMock->method('getRequest')->willReturn($requestMock);
        $requestMock->method('isXmlHttpRequest')->willReturn(true);
        $requestMock->method('getUri')->willReturn('some_login_uri');

        $listener = new RequestListener();
        $listener->onRequest($eventMock);
    }
}
