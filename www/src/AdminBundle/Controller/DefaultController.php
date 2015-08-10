<?php

/* Copyright (C) 2015 Michael Giesler
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
 * @package AdminBundle
 */

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_mainpage")
     *
     * @return string
     */
    public function indexAction()
    {
        return $this->render('AdminBundle::index.html.twig');
    }

    /**
     * @Route("/datatable", name="admin_datatable")
     *
     * @return String
     */
    public function datatableAction()
    {
        return new Response('[
    { id:"1", person: "Nanny", place: "Alabama", age: "45" },
    { id:"2", person: "Derek", place: "New York", age: "23" },
    { id:"3", person: "Samuel", place: "Oregon", age: "32"}
]');
    }

}
