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

use DembeloMain\Document\Licensee;
use DembeloMain\Model\Repository\Doctrine\ODM\AbstractRepository;
use DembeloMain\Document\Importfile;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use StdClass;
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
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textNodeRepository;
    /**
     * @var string
     */
    private $topicImageDirectory;

    /**
     * DefaultController constructor.
     * @param Templating $templating
     * @param UserRepositoryInterface $userRepository
     * @param LicenseeRepositoryInterface $licenseeRepository
     * @param string $configTwineDirectory
     */
    public function __construct(
        Templating $templating,
        UserRepositoryInterface $userRepository,
        LicenseeRepositoryInterface $licenseeRepository,
        TopicRepositoryInterface $topicRepository,
        ImportfileRepositoryInterface $importfileRepository,
        TextNodeRepositoryInterface $textNodeRepository,
        UserPasswordEncoder $userPasswordEncoder,
        string $configTwineDirectory,
        string $topicImageDirectory,
        \Swift_Mailer $mailer
    ) {
        $this->configTwineDirectory = $configTwineDirectory;
        $this->userRepository = $userRepository;
        $this->templating = $templating;
        $this->licenseeRepository = $licenseeRepository;
        $this->topicRepository = $topicRepository;
        $this->importfileRepository = $importfileRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->mailer = $mailer;
        $this->textNodeRepository = $textNodeRepository;
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
     * @Route("/users", name="admin_users")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function usersAction(Request $request): Response
    {
        $filters = $request->query->get('filter');

        /* @var $query QueryBuilder */
        $query = $this->userRepository->createQueryBuilder();
        if (null !== $filters) {
            foreach ($filters as $field => $value) {
                if (empty($value) && $value !== '0') {
                    continue;
                }
                if ($field === 'status') {
                    //$value = $value === 'aktiv' ? 1 : 0;
                    $query->field($field)->equals((int) $value);
                } else {
                    $query->field($field)->equals(new \MongoRegex('/.*'.$value.'.*/i'));
                }
            }
        }
        $users = $query->getQuery()->execute();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users as $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->email = $user->getEmail();
            $obj->roles = implode(', ', $user->getRoles());
            $obj->licenseeId = is_null($user->getLicenseeId()) ? '' : $user->getLicenseeId();
            $obj->gender = $user->getGender();
            $obj->status = $user->getStatus(); // === 0 ? 'inaktiv' : 'aktiv';
            $obj->source = $user->getSource();
            $obj->reason = $user->getReason();
            $obj->created = date('Y-m-d H:i:s', $user->getMetadata()['created']);
            $obj->updated = date('Y-m-d H:i:s', $user->getMetadata()['updated']);
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
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
            $obj = new StdClass();
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

    /**
     * @Route("/topicSuggest", name="admin_topic_suggest")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function topicSuggestAction(Request $request): Response
    {
        $filter = $request->query->get('filter');

        $searchString = $filter['value'];

        /* @var $topics \DembeloMain\Document\Topic[] */
        $topics = $this->topicRepository->findBy(array('name' => new \MongoRegex('/'.$searchString.'/')), null, 10);

        $output = [];
        foreach ($topics as $topic) {
            $output[] = array(
                'id' => $topic->getId(),
                'value' => $topic->getName(),
            );
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/topics", name="admin_topics")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function topicsAction(): Response
    {
        $users = $this->topicRepository->findAll();

        $output = [];
        /* @var $user \DembeloMain\Document\Topic */
        foreach ($users as $user) {
            $obj = new StdClass();
            $obj->id = $user->getId();
            $obj->name = $user->getName();
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/save", name="admin_formsave")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function formsaveAction(Request $request): Response
    {
        $params = $request->request->all();

        if (!isset($params['formtype']) || !in_array($params['formtype'], array('user', 'licensee', 'topic', 'importfile', 'textnode'))) {
            return new Response(\json_encode(array('error' => true)));
        }
        if (!isset($params['id'])) {
            return new Response(\json_encode(array('error' => true)));
        }
        $formtype = $params['formtype'];

        /* @var $repository AbstractRepository */
        switch($formtype) {
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
                throw new \Exception('unknown formtype ['.$formtype.']');
        }

        if (isset($params['id']) && $params['id'] == 'new') {
            $className = $repository->getClassName();
            $item = new $className();
        } else {
            $item = $repository->find($params['id']);
            if (is_null($item) || $item->getId() != $params['id']) {
                return new Response(\json_encode(array('error' => true)));
            }
        }

        foreach ($params as $param => $value) {
            if (in_array($param, array('id', 'formtype', 'filename', 'orgname'))) {
                continue;
            }
            if ($param == 'password' && empty($value)) {
                continue;
            } elseif ($param == 'password') {
                $value = $this->userPasswordEncoder->encodePassword($item, $value);
            } elseif ($param == 'licenseeId' && $value === '') {
                $value = null;
            } elseif ($param === 'imported' && $value === '') {
                $value = null;
            }
            $method = 'set'.ucfirst($param);
            if (method_exists($item, $method)) {
                $item->$method($value);
            }
        }
        //var_dump($item);die();
        if (method_exists($item, 'setMetadata')) {
            $item->setMetadata('updated', time());
        }
        $repository->save($item);

        if ($formtype === 'topic' && array_key_exists('imageFileName', $params) && !is_null($params['imageFileName'])) {
            $this->saveTopicImage($item, $params['imageFileName'], $params['originalImageName']);
            $repository->save($item);
        }

        if ($formtype == 'importfile' && array_key_exists('filename', $params)) {
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
     * @Route("/useractivationmail", name="admin_user_activation_mail")
     *
     * @param Request $request
     * @return Response
     */
    public function useractivationmailAction(Request $request): Response
    {
        $userId = $request->request->get('userId');

        /* @var $user \DembeloMain\Document\User */
        $user = $this->userRepository->find($userId);
        if (null === $user) {
            return new Response(\json_encode(['error' => false]));
        }
        $user->setActivationHash(sha1($user->getEmail().$user->getPassword().\time()));

        $this->userRepository->save($user);

        $message = (new \Swift_Message('waszulesen - Bestätigung der Email-Adresse'))
            ->setFrom('system@waszulesen.de')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                    'AdminBundle::Emails/registration.txt.twig',
                    array('hash' => $user->getActivationHash())
                ),
                'text/html'
            );

        $this->mailer->send($message);

        return new Response(\json_encode(['error' => false]));
    }

    /**
     * @Route("/textnodes", name="admin_textnodes")
     *
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function textnodesAction(): Response
    {
        $textnodes = $this->textNodeRepository->findAll();

        $licenseeIndex = $this->buildLicenseeIndex();
        $importfileIndex = $this->buildImportfileIndex();

        $output = [];
        /* @var $textnode \DembeloMain\Document\Textnode */
        foreach ($textnodes as $textnode) {
            $obj = new StdClass();
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

    private function buildLicenseeIndex(): array
    {
        $licensees = $this->licenseeRepository->findAll();
        $index = [];
        foreach ($licensees as $licensee) {
            $index[$licensee->getId()] = $licensee->getName();
        }

        return $index;
    }

    private function buildImportfileIndex(): array
    {
        $importfiles = $this->importfileRepository->findAll();
        $index = [];
        foreach ($importfiles as $importfile) {
            $index[$importfile->getID()] = $importfile->getName();
        }

        return $index;
    }

    /**
     * saves temporary file to final place
     *
     * @param Topic $item topic instance
     * @param string $filename filename hash
     * @param string $orgname original name
     */
    private function saveTopicImage(Topic $item, $filename, $orgname)
    {
        if (empty($filename) || empty($orgname)) {
            return;
        }
        $directory = $this->topicImageDirectory;
        $finalDirectory = $directory.$item->getId().'/';
        if (!is_dir($finalDirectory)) {
            mkdir($finalDirectory);
        }
        $finalName = $finalDirectory.$orgname;
        $file = $directory.$filename;
        rename($file, $finalName);
        $item->setOriginalImageName($orgname);
        $item->setImageFilename($finalName);
    }

    private function formatMetadata(array $metadata): string
    {
        $string = '';
        foreach ($metadata as $key => $value) {
            $string .= $key.': '.$value."\n";
        }

        return $string;
    }
}
