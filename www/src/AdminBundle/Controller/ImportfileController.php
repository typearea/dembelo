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

use AdminBundle\Model\ImportTwine;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class ImportController
 * @Route(service="app.admin_controller_importfile")
 */
class ImportfileController extends Controller
{
    /**
     * @var ImportfileRepositoryInterface
     */
    private $importfileRepository;

    /**
     * @var ImportTwine
     */
    private $importTwine;

    /**
     * @var ManagerRegistry
     */
    private $mongoDb;

    /**
     * @var
     */
    private $configTwineDirectory;

    /**
     * ImportController constructor.
     * @param ImportfileRepositoryInterface $importfileRepository
     * @param ImportTwine                   $importTwine
     * @param ManagerRegistry               $mongoDb
     * @param string                        $configTwineDirectory
     */
    public function __construct(ImportfileRepositoryInterface $importfileRepository, ImportTwine $importTwine, ManagerRegistry $mongoDb, string $configTwineDirectory)
    {
        $this->importfileRepository = $importfileRepository;
        $this->importTwine = $importTwine;
        $this->mongoDb = $mongoDb;
        $this->configTwineDirectory = $configTwineDirectory;
    }

    /**
     * @Route("/importfiles", name="admin_importfiles")
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function importfilesAction(): Response
    {
        $importfiles = $this->importfileRepository->findAll();

        $output = array();
        /* @var $importfile \DembeloMain\Document\Importfile */
        foreach ($importfiles as $importfile) {
            $importfileData = [];
            $importfileData['id'] = $importfile->getId();
            $importfileData['name'] = $importfile->getName();
            $importfileData['author'] = $importfile->getAuthor();
            $importfileData['publisher'] = $importfile->getPublisher();
            $importfileData['imported'] = $importfile->getImported();
            $importfileData['orgname'] = $importfile->getOriginalname();
            $importfileData['licenseeId'] = $importfile->getLicenseeId();
            $importfileData['topicId'] = $importfile->getTopicId();
            $output[] = $importfileData;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/import", name="admin_import")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws InvalidArgumentException
     */
    public function importAction(Request $request): Response
    {
        $importfileId = $request->get('importfileId');

        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager*/
        $dm = $this->mongoDb->getManager();

        /* @var $importfile \DembeloMain\Document\Importfile */
        $importfile = $this->importfileRepository->find($importfileId);
        try {
            if (null === $importfile) {
                throw new \RuntimeException(sprintf('file with id [%s] not found', $importfileId));
            }
            $returnValue = $this->importTwine->run($importfile);

            $dm->flush();
            $output = [
                'success' => true,
                'returnValue' => $returnValue,
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return new Response(json_encode($output));
    }

    /**
     * @Route("/uploadimportfile", name="admin_upload_file")
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function uploadImportfileAction(): Response
    {
        $output = array();

        $file = $_FILES['upload'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $output['status'] = 'error';

            return new Response(\json_encode($output));
        }

        $filename = md5(uniqid('', true).$file['name']);

        move_uploaded_file($file['tmp_name'], $this->configTwineDirectory.$filename);

        $output['filename'] = $filename;
        $output['orgname'] = $file['name'];

        $output['status'] = 'server';

        return new Response(\json_encode($output));
    }
}
