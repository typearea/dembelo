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
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use AdminBundle\Model\ImportTwine;
use DembeloMain\Document\Importfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use StdClass;
use DembeloMain\Document\User;
use DembeloMain\Document\Topic;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

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
        $mainMenuData = [
            ['id' => "1", 'type' => "folder", 'value' => "Benutzer", 'css' => "folder_music"],
            ['id' => "2", 'type' => "folder", 'value' => "Lizenznehmer", 'css' => "folder_music"],
            ['id' => "3", 'type' => "folder", 'value' => "Themenfelder", 'css' => "folder_music"],
            ['id' => "4", 'type' => "folder", 'value' => "Importe", 'css' => "folder_music"],
            ['id' => "5", 'type' => "folder", 'value' => "Textknoten", 'css' => "folder_music"],
        ];

        $jsonEncoder = new JsonEncoder();

        return $this->render('AdminBundle::index.html.twig', array('mainMenuData' => $jsonEncoder->encode($mainMenuData, 'json')));
    }

    /**
     * @Route("/users", name="admin_users")
     *
     * @param Request $request
     * @return String
     */
    public function usersAction(Request $request)
    {
        $repository = $this->get('app.model_repository_user');

        $filters = $request->query->get('filter');

        $query = $repository->createQueryBuilder();
        if (!is_null($filters)) {
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
            $obj->roles = join(', ', $user->getRoles());
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
     * @return String
     */
    public function licenseesAction()
    {
        $repository = $this->get('app.model_repository_licensee');

        $licensees = $repository->findAll();

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
     * @return String
     */
    public function licenseeSuggestAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $searchString = $filter['value'];

        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Licensee');

        $licensees = $repository->findBy(array('name' => new \MongoRegex('/'.$searchString.'/')), null, 10);

        $output = array();
        /* @var $licensee \DembeloMain\Document\Licensee */
        foreach ($licensees as $licensee) {
            $output[] = array(
                'id' => $licensee->getId(),
                'value' => $licensee->getName(),
            );
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/topics", name="admin_topics")
     *
     * @return String
     */
    public function topicsAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Topic');

        $users = $repository->findAll();

        $output = array();
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
     * @return String
     */
    public function formsaveAction(Request $request)
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
        $repository = $this->get('app.model_repository_'.$formtype);

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
                $encoder = $this->get('security.password_encoder');
                $value = $encoder->encodePassword($item, $value);
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
     * @return String
     */
    public function useractivationmailAction(Request $request)
    {
        $userId = $request->request->get('userId');

        /* @var $mongo \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $mongo = $this->get('doctrine_mongodb');
        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager*/
        $dm = $mongo->getManager();

        $repository = $mongo->getRepository('DembeloMain:User');

        /* @var $user \DembeloMain\Document\User */
        $user = $repository->find($userId);
        if (null === $user) {
            return new Response(\json_encode(['error' => false]));
        }
        $user->setActivationHash(sha1($user->getEmail().$user->getPassword().\time()));

        $dm->persist($user);
        $dm->flush();

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

        $this->get('mailer')->send($message);

        return new Response(\json_encode(['error' => false]));
    }

    /**
     * @Route("/importfiles", name="admin_importfiles")
     *
     * @return Response
     */
    public function importfilesAction()
    {
        $mongo = $this->get('doctrine_mongodb');
        /* @var $repository \Doctrine\ODM\MongoDB\DocumentRepository */
        $repository = $mongo->getRepository('DembeloMain:Importfile');

        $importfiles = $repository->findAll();

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
            $output[] = $importfileData;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/uploadimportfile", name="admin_upload_file")
     *
     * @return Response
     */
    public function uploadImportfileAction()
    {
        $output = array();

        $file = $_FILES['upload'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $output['status'] = 'error';

            return new Response(\json_encode($output));
        }

        $directory = $this->container->getParameter('twine_directory');

        $filename = md5(uniqid().$file['name']);

        move_uploaded_file($file["tmp_name"], $directory.$filename);

        $output['filename'] = $filename;
        $output['orgname'] = $file['name'];

        $output['status'] = 'server';

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/textnodes", name="admin_textnodes")
     *
     * @return Response
     */
    public function textnodesAction()
    {
        $repository = $this->get('app.model_repository_textNode');
        $textnodes = $repository->findAll();

        $licenseeIndex = $this->buildLicenseeIndex();
        $importfileIndex = $this->buildImportfileIndex();

        $output = array();
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
     * @Route("/import", name="admin_import")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request)
    {
        $importfileId = $request->get('importfileId');

        /* @var $mongo \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $mongo = $this->get('doctrine_mongodb');
        /* @var $dm \Doctrine\ODM\MongoDB\DocumentManager*/
        $dm = $mongo->getManager();

        $repository = $mongo->getRepository('DembeloMain:Importfile');

        /* @var $importfile \DembeloMain\Document\Importfile */
        $importfile = $repository->find($importfileId);
        $importer = $this->get('admin.import.twine');
        try {
            $returnValue = $importer->run($importfile);

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
     * saves temporary file to final place
     *
     * @param Importfile $item importfile instance
     * @param string $filename filename hash
     * @param string $orgname original name
     */
    private function saveFile(Importfile $item, $filename, $orgname)
    {
        if (empty($filename) || empty($orgname)) {
            return;
        }

        $directory = $this->container->getParameter('twine_directory');
        $finalDirectory = $directory.$item->getLicenseeId().'/';
        if (!is_dir($finalDirectory)) {
            mkdir($finalDirectory);
        }
        $finalName = $finalDirectory.$item->getId();
        $file = $directory.$filename;
        rename($file, $finalName);

        $item->setOriginalname($orgname);
        $item->setFilename($finalName);
    }

    private function buildLicenseeIndex()
    {
        $repository = $this->get('app.model_repository_licensee');
        $licensees = $repository->findAll();
        $index = [];
        foreach ($licensees as $licensee) {
            $index[$licensee->getID()] = $licensee->getName();
        }

        return $index;
    }

    private function buildImportfileIndex()
    {
        $repository = $this->get('app.model_repository_importfile');
        $importfiles = $repository->findAll();
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
        $directory = $this->container->getParameter('topic_image_directory');
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

    private function formatMetadata(array $metadata)
    {
        $string = '';
        foreach ($metadata as $key => $value) {
            $string .= $key.': '.$value."\n";
        }

        return $string;
    }
}
