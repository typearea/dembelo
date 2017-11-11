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

namespace AdminBundle\Controller;

use DembeloMain\Document\Licensee;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LicenseeController
 * @Route(service="app.admin_controller_licensee")
 */
class LicenseeController extends Controller
{
    /**
     * @var LicenseeRepositoryInterface
     */
    private $licenseeRepository;

    /**
     * UserController constructor.
     * @param LicenseeRepositoryInterface       $licenseeRepository
     */
    public function __construct(
        LicenseeRepositoryInterface $licenseeRepository
    ) {
        $this->licenseeRepository = $licenseeRepository;
    }

    /**
     * @Route("/licensees", name="admin_licensees")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function licenseesAction(): Response
    {
        $licensees = $this->licenseeRepository->findAll();

        $output = array();
        /* @var $licensee \DembeloMain\Document\Licensee */
        foreach ($licensees as $licensee) {
            $obj = new \stdClass();
            $obj->id = $licensee->getId();
            $obj->name = $licensee->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/licenseeSuggest", name="admin_licensee_suggest")
     *
     * @param Request $request
     * @return Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function licenseeSuggestAction(Request $request): Response
    {
        $filter = $request->query->get('filter');

        $searchString = $filter['value'];

        /* @var $licensees Licensee[] */
        $licensees = $this->licenseeRepository->findBy(['name' => new \MongoRegex('/'.$searchString.'/')], null, 10);

        $output = [];
        foreach ($licensees as $licensee) {
            $output[] = array(
                'id' => $licensee->getId(),
                'value' => $licensee->getName(),
            );
        }

        return new Response(\json_encode($output));
    }
}