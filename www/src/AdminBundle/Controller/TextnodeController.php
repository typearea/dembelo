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

use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TextnodeController
 * @Route(service="app.admin_controller_textnode")
 */
class TextnodeController
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
    public function __construct(TextnodeRepositoryInterface $textnodeRepository, ImportfileRepositoryInterface $importfileRepository, LicenseeRepositoryInterface $licenseeRepository)
    {
        $this->textnodeRepository = $textnodeRepository;
        $this->importfileRepository = $importfileRepository;
        $this->licenseeRepository = $licenseeRepository;
    }

    /**
     * @Route("/textnodes", name="admin_textnodes")
     *
     * @return Response
     *
     * @throws \InvalidArgumentException
     */
    public function textnodesAction(): Response
    {
        $textnodes = $this->textnodeRepository->findAll();

        $licenseeIndex = $this->buildLicenseeIndex();
        $importfileIndex = $this->buildImportfileIndex();

        $output = [];
        foreach ($textnodes as $textnode) {
            $obj = new \stdClass();
            $obj->id = $textnode->getId();
            $obj->status = $textnode->getStatus() ? 'aktiv' : 'inaktiv';
            $obj->created = $textnode->getCreated()->format('d.m.Y, H:i:s');
            $obj->access = $textnode->getAccess() ? 'ja' : 'nein';
            $obj->licensee = $licenseeIndex[$textnode->getLicenseeId()];
            $obj->importfile = $importfileIndex[$textnode->getImportfileId()] ?? 'unbekannt';
            $obj->beginning = substr(htmlentities(strip_tags($textnode->getText())), 0, 200).'...';
            $obj->financenode = $textnode->isFinanceNode() ? 'ja' : 'nein';
            $obj->arbitraryId = $textnode->getArbitraryId();
            $obj->twineId = $textnode->getTwineId();
            $obj->metadata = $this->formatMetadata($textnode->getMetadata());
            $obj->parentnodes = $this->buildHitchString($textnode->getParentHitches(), 'parent');
            $obj->childnodes = $this->buildHitchString($textnode->getChildHitches(), 'child');
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @param TextnodeHitch[]|Collection $hitches
     * @param string                     $direction
     *
     * @return string
     */
    private function buildHitchString(Collection $hitches, string $direction): string
    {
        $string = '';
        $counter = 0;
        foreach ($hitches as $hitch) {
            ++$counter;

            if ('parent' === $direction) {
                $arbitraryId = $hitch->getSourceTextnode()->getArbitraryId();
            } else {
                $arbitraryId = $hitch->getTargetTextnode()->getArbitraryId();
            }

            $string .= $counter.') '.$hitch->getDescription().' ['.$arbitraryId.']'."\n";
        }

        return $string;
    }

    /**
     * @return array
     */
    private function buildLicenseeIndex(): array
    {
        $licensees = $this->licenseeRepository->findAll();
        $index = [];
        foreach ($licensees as $licensee) {
            $index[$licensee->getId()] = $licensee->getName();
        }

        return $index;
    }

    /**
     * @param array $metadata
     *
     * @return string
     */
    private function formatMetadata(array $metadata): string
    {
        $string = '';
        foreach ($metadata as $key => $value) {
            $string .= $key.': '.$value."\n";
        }

        return $string;
    }

    /**
     * @return array
     */
    private function buildImportfileIndex(): array
    {
        /* @var $importfiles \DembeloMain\Document\Importfile[] */
        $importfiles = $this->importfileRepository->findAll();
        $index = [];
        foreach ($importfiles as $importfile) {
            $index[$importfile->getId()] = $importfile->getName();
        }

        return $index;
    }
}
