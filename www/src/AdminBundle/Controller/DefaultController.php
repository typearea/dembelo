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

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\AbstractRepository;
use DembeloMain\Document\Importfile;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DembeloMain\Document\Topic;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;

/**
 * Class DefaultController
 * @Route(service="app.admin_controller_default")
 */
class DefaultController extends Controller
{
    /**
     * @var string
     */
    private $configTwineDirectory;

    /**
     * @var Templating
     */
    private $templating;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var LicenseeRepositoryInterface
     */
    private $licenseeRepository;

    /**
     * @var TopicRepositoryInterface
     */
    private $topicRepository;

    /**
     * @var ImportfileRepositoryInterface
     */
    private $importfileRepository;

    /**
     * @var UserPasswordEncoder
     */
    private $userPasswordEncoder;

    /**
     * @var string
     */
    private $topicImageDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * DefaultController constructor.
     * @param Templating                    $templating
     * @param UserRepositoryInterface       $userRepository
     * @param LicenseeRepositoryInterface   $licenseeRepository
     * @param TopicRepositoryInterface      $topicRepository
     * @param ImportfileRepositoryInterface $importfileRepository
     * @param UserPasswordEncoder           $userPasswordEncoder
     * @param string                        $configTwineDirectory
     * @param string                        $topicImageDirectory
     * @param Filesystem                    $filesystem
     */
    public function __construct(
        Templating $templating,
        UserRepositoryInterface $userRepository,
        LicenseeRepositoryInterface $licenseeRepository,
        TopicRepositoryInterface $topicRepository,
        ImportfileRepositoryInterface $importfileRepository,
        UserPasswordEncoder $userPasswordEncoder,
        string $configTwineDirectory,
        string $topicImageDirectory,
        Filesystem $filesystem
    ) {
        $this->configTwineDirectory = $configTwineDirectory;
        $this->userRepository = $userRepository;
        $this->templating = $templating;
        $this->licenseeRepository = $licenseeRepository;
        $this->topicRepository = $topicRepository;
        $this->importfileRepository = $importfileRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->topicImageDirectory = $topicImageDirectory;
        $this->filesystem = $filesystem;
    }

    /**
     * @Route("/", name="admin_mainpage")
     *
     * @return Response
     * @throws \RuntimeException
     */
    public function indexAction(): Response
    {
        $mainMenuData = [
            ['id' => '1', 'type' => 'folder', 'value' => 'Benutzer', 'css' => 'folder_music'],
            ['id' => '2', 'type' => 'folder', 'value' => 'Lizenznehmer', 'css' => 'folder_music'],
            ['id' => '3', 'type' => 'folder', 'value' => 'Themenfelder', 'css' => 'folder_music'],
            ['id' => '4', 'type' => 'folder', 'value' => 'Importe', 'css' => 'folder_music'],
            ['id' => '5', 'type' => 'folder', 'value' => 'Textknoten', 'css' => 'folder_music'],
        ];

        $jsonEncoder = new JsonEncoder();

        return $this->templating->renderResponse(
            'AdminBundle::index.html.twig',
            [
                'mainMenuData' => $jsonEncoder->encode($mainMenuData, 'json'),
            ]
        );
    }

    /**
     * @Route("/save", name="admin_formsave")
     *
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function formsaveAction(Request $request): Response
    {
        $params = $request->request->all();

        if (!isset($params['id'])) {
            return new Response(\json_encode(array('error' => true)));
        }
        $formtype = $params['formtype'] ?? '';

        /* @var $repository AbstractRepository */
        switch ($formtype) {
            case 'user':
                $repository = $this->userRepository;
                break;
            case 'topic':
                $repository = $this->topicRepository;
                break;
            case 'licensee':
                $repository = $this->licenseeRepository;
                break;
            case 'importfile':
                $repository = $this->importfileRepository;
                break;
            default:
                return new Response(\json_encode(array('error' => true)));
        }

        if (isset($params['id']) && $params['id'] === 'new') {
            $className = $repository->getClassName();
            $item = new $className();
        } else {
            $item = $repository->find($params['id']);
            if (null === $item || $item->getId() !== $params['id']) {
                return new Response(\json_encode(array('error' => true)));
            }
        }

        foreach ($params as $param => $value) {
            if (in_array($param, array('id', 'formtype', 'filename', 'orgname'), true)) {
                continue;
            }
            if ($param === 'password' && empty($value)) {
                continue;
            }

            if ($item instanceof User && $param === 'password') {
                $value = $this->userPasswordEncoder->encodePassword($item, $value);
            } elseif ($value === '' && in_array($param, ['licenseeId', 'imported'], true)) {
                $value = null;
            }
            $method = 'set'.ucfirst($param);
            if (method_exists($item, $method)) {
                $item->$method($value);
            }
        }

        if (method_exists($item, 'setMetadata')) {
            $item->setMetadata('updated', time());
        }
        $repository->save($item);

        if ($item instanceof Topic
            && array_key_exists('imageFileName', $params)
            && array_key_exists('originalImageName', $params)
            && !empty($params['imageFileName'])
            && !empty($params['originalImageName'])
        ) {
            $this->saveTopicImage($item, $params['imageFileName'], $params['originalImageName']);
            $repository->save($item);
        }

        if ($item instanceof Importfile
            && array_key_exists('filename', $params)
            && array_key_exists('orgname', $params)
            && !empty($params['filename'])
            && !empty($params['orgname'])
        ) {
            $this->saveFile($item, $params['filename'], $params['orgname']);
            $repository->save($item);
        }

        $output = array(
            'error' => false,
            'newId' => $item->getId(),
        );

        return new Response(\json_encode($output));
    }

    /**
     * saves temporary file to final place
     *
     * @param Importfile $item importfile instance
     * @param string $filename filename hash
     * @param string $orgname original name
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    private function saveFile(Importfile $item, $filename, $orgname)
    {
        $directory = $this->configTwineDirectory;
        $file = $directory.$filename;
        if (!$this->filesystem->exists($file)) {
            return;
        }
        $finalDirectory = $directory.$item->getLicenseeId().'/';
        $this->filesystem->mkdir($finalDirectory);
        $finalName = $finalDirectory.$item->getId();

        $this->filesystem->rename($file, $finalName);

        $item->setOriginalname($orgname);
        $item->setFilename($finalName);
    }

    /**
     * saves temporary file to final place
     *
     * @param Topic $item topic instance
     * @param string $filename filename hash
     * @param string $orgname original name
     * @throws \RuntimeException
     */
    private function saveTopicImage(Topic $item, string $filename, string $orgname)
    {
        $directory = $this->topicImageDirectory;
        $finalDirectory = $directory.$item->getId().'/';
        $this->filesystem->mkdir($finalDirectory);

        $finalName = $finalDirectory.$orgname;
        $file = $directory.$filename;
        $this->filesystem->rename($file, $finalName);
        $item->setOriginalImageName($orgname);
        $item->setImageFilename($finalName);
    }
}
