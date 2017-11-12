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

use DembeloMain\Model\Repository\Doctrine\ODM\AbstractRepository;
use DembeloMain\Document\Importfile;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * DefaultController constructor.
     * @param Templating                    $templating
     * @param UserRepositoryInterface       $userRepository
     * @param LicenseeRepositoryInterface   $licenseeRepository
     * @param TopicRepositoryInterface      $topicRepository
     * @param ImportfileRepositoryInterface $importfileRepository
     * @param UserPasswordEncoder           $userPasswordEncoder
     * @param string                        $configTwineDirectory
     * @param string                        $topicImageDirectory
     */
    public function __construct(
        Templating $templating,
        UserRepositoryInterface $userRepository,
        LicenseeRepositoryInterface $licenseeRepository,
        TopicRepositoryInterface $topicRepository,
        ImportfileRepositoryInterface $importfileRepository,
        UserPasswordEncoder $userPasswordEncoder,
        string $configTwineDirectory,
        string $topicImageDirectory
    ) {
        $this->configTwineDirectory = $configTwineDirectory;
        $this->userRepository = $userRepository;
        $this->templating = $templating;
        $this->licenseeRepository = $licenseeRepository;
        $this->topicRepository = $topicRepository;
        $this->importfileRepository = $importfileRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->topicImageDirectory = $topicImageDirectory;
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
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function formsaveAction(Request $request): Response
    {
        $params = $request->request->all();

        if (!isset($params['formtype']) || !in_array($params['formtype'], array('user', 'licensee', 'topic', 'importfile', 'textnode'), true)) {
            return new Response(\json_encode(array('error' => true)));
        }
        if (!isset($params['id'])) {
            return new Response(\json_encode(array('error' => true)));
        }
        $formtype = $params['formtype'];

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
                throw new \RuntimeException('unknown formtype ['.$formtype.']');
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

            if ($param === 'password') {
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

        if ($formtype === 'topic' && array_key_exists('imageFileName', $params) && null !== $params['imageFileName']) {
            $this->saveTopicImage($item, $params['imageFileName'], $params['originalImageName']);
            $repository->save($item);
        }

        if ($item instanceof Importfile && array_key_exists('filename', $params)) {
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
     */
    private function saveFile(Importfile $item, $filename, $orgname)
    {
        if (empty($filename) || empty($orgname)) {
            return;
        }

        $directory = $this->configTwineDirectory;
        $file = $directory.$filename;
        if (!file_exists($file)) {
            return;
        }
        $finalDirectory = $directory.$item->getLicenseeId().'/';
        if (!is_dir($finalDirectory)) {
            if (!mkdir($finalDirectory) && !is_dir($finalDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $finalDirectory));
            }
        }
        $finalName = $finalDirectory.$item->getId();

        rename($file, $finalName);

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
    private function saveTopicImage(Topic $item, $filename, $orgname)
    {
        if (empty($filename) || empty($orgname)) {
            return;
        }
        $directory = $this->topicImageDirectory;
        $finalDirectory = $directory.$item->getId().'/';
        if (!is_dir($finalDirectory)) {
            if (!mkdir($finalDirectory) && !is_dir($finalDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $finalDirectory));
            }
        }
        $finalName = $finalDirectory.$orgname;
        $file = $directory.$filename;
        rename($file, $finalName);
        $item->setOriginalImageName($orgname);
        $item->setImageFilename($finalName);
    }
}
