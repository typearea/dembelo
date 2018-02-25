<?php
/* Copyright (C) 2016 Michael Giesler
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
namespace DembeloMain\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RequestListener
 */
class RequestListener
{
    /**
     * modifies response when session does not exist in ajax call
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function onRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return; //we dismiss requests other than ajax
        }

        if (strpos($request->getUri(), 'login')) {
            $response = new JsonResponse(['session_expired' => true]);
            $event->setResponse($response);
        }
    }
}
