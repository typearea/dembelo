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

use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TextnodeController
 * @Route(service="app.admin_controller_textnode")
 */
class TextnodeController extends Controller
{
    /**
     * @var TextNodeRepositoryInterface
     */
    private $textnodeRepository;

    /**
     * @var ImportfileRepositoryInterface
     */
    private $importfileRepository;

    /**
     * @var LicenseeRepositoryInterface
     */
    private $licenseeRepository;

    /**
     * TextnodeController constructor.
     * @param TextnodeRepositoryInterface   $textnodeRepository
     * @param ImportfileRepositoryInterface $importfileRepository
     * @param LicenseeRepositoryInterface   $licenseeRepository
     */
    public function __construct(
        TextnodeRepositoryInterface $textnodeRepository,
        ImportfileRepositoryInterface $importfileRepository,
        LicenseeRepositoryInterface $licenseeRepository
    ) {
        $this->textnodeRepository = $textnodeRepository;
        $this->importfileRepository = $importfileRepository;
        $this->licenseeRepository = $licenseeRepository;
    }

    /**
     * @Route("/textnodes", name="admin_textnodes")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function textnodesAction(): Response
    {
        /* @var $textnodes \DembeloMain\Document\Textnode[] */
        $textnodes = $this->textnodeRepository->findAll();

        $licenseeIndex = $this->buildLicenseeIndex();
        $importfileIndex = $this->buildImportfileIndex();

        $output = [];
        /* @var $textnode \DembeloMain\Document\Textnode */
        foreach ($textnodes as $textnode) {
            $obj = new \stdClass();
            $obj->id = $textnode->getId();
            $obj->arbitraryId = $textnode->getArbitraryId();
            $obj->created = $textnode->getCreated()->format('d.m.Y, H:i:s');
            $obj->status = $textnode->getStatus() ? 'aktiv' : 'inaktiv';
            $obj->access = $textnode->getAccess() ? 'ja' : 'nein';
            $obj->licensee = $licenseeIndex[$textnode->getLicenseeId()];
            $obj->importfile = isset($importfileIndex[$textnode->getImportfileId()]) ? $importfileIndex[$textnode->getImportfileId()] : 'unbekannt';
            $obj->beginning = substr(htmlentities(strip_tags($textnode->getText())), 0, 200)."...";
            $obj->financenode = $textnode->isFinanceNode() ? 'ja' : 'nein';
            $obj->twineId = $textnode->getTwineId();
            $obj->metadata = $this->formatMetadata($textnode->getMetadata());
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    private function buildLicenseeIndex(): array
    {
        $licensees = $this->licenseeRepository->findAll();
        $index = [];
        foreach ($licensees as $licensee) {
            $index[$licensee->getId()] = $licensee->getName();
        }

        return $index;
    }

    private function formatMetadata(array $metadata): string
    {
        $string = '';
        foreach ($metadata as $key => $value) {
            $string .= $key.': '.$value."\n";
        }

        return $string;
    }

    private function buildImportfileIndex(): array
    {
        /* @var $importfiles \DembeloMain\Document\Importfile[] */
        $importfiles = $this->importfileRepository->findAll();
        $index = [];
        foreach ($importfiles as $importfile) {
            $index[$importfile->getID()] = $importfile->getName();
        }

        return $index;
    }
}
