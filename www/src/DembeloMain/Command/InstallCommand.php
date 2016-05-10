<?php
/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
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
 * @package DembeloMain
 */

namespace DembeloMain\Command;

use DembeloMain\Document\Licensee;
use DembeloMain\Document\Textnode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DembeloMain\Document\User;
use DembeloMain\Document\Topic;
use DembeloMain\Document\Story;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class InstallCommand
 * @package DembeloMain
 */
class InstallCommand extends ContainerAwareCommand
{

    private $dummyData = array();

    protected function configure()
    {
        $this
            ->setName('dembelo:install')
            ->setDescription('Installation Routine')
            ->addOption(
                'purge-db',
                null,
                InputOption::VALUE_NONE,
                'deletes all content from DB before installation'
            )
            ->addOption(
                'with-dummy-data',
                null,
                InputOption::VALUE_NONE,
                'installs some dummy data to play with'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($input->getOption('purge-db')) {
            $this->purgeDB($output);
            $output->writeln("<info>Database cleared</info>");
        }

        $this->installDefaultUsers($output);
        $output->writeln("<info>Default users installed</info>");

        if ($input->getOption('with-dummy-data')) {
            $this->installDummyData($output);
            $output->writeln("<info>Dummy data installed</info>");
        }

    }

    protected function purgeDB()
    {
        $collectionClasses = array(
            'DembeloMain\Document\Licensee',
            'DembeloMain\Document\Readpath',
            'DembeloMain\Document\Textnode',
            'DembeloMain\Document\Topic',
            'DembeloMain\Document\User',
        );

        $mongo = $this->getContainer()->get('doctrine_mongodb');
        $dm = $mongo->getManager();

        foreach ($collectionClasses as $collectionClass) {
            $collection = $dm->getDocumentCollection($collectionClass);
            $collection->remove(array());
        }

    }

    protected function installDefaultUsers(OutputInterface $output)
    {
        $this->installAdminUser();
        $output->writeln('admin user installed');
    }

    protected function installAdminUser()
    {
        $mongo = $this->getContainer()->get('doctrine_mongodb');

        $dm = $mongo->getManager();

        $users = array(
            array(
                'email' => 'admin@dembelo.tld',
                'password' => 'dembelo',
                'roles' => array('ROLE_ADMIN'),
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => array('created' => time(), 'updated' => time()),
            ),
        );

        $this->installUsers($users, $mongo, $dm);
    }

    protected function installDummyData(OutputInterface $output)
    {

        $mongo = $this->getContainer()->get('doctrine_mongodb');

        $dm = $mongo->getManager();

        $this->createLicensees($mongo, $dm);
        $output->writeln("Licensees installed...");

        $this->createUsers($mongo, $dm);
        $output->writeln("Users installed...");

        $this->createTopics($mongo, $dm);
        $output->writeln("Topics installed...");

        $this->createTextnodes($mongo, $dm);
        $output->writeln("Textnodes installed...");

        $this->createHitches($mongo, $dm);
        $output->writeln("Hitches installed...");

        $dm->flush();

    }

    private function createLicensees(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Licensee');

        $licensees = array(
            array('name' => 'Lizenznehmer 1'),
            array('name' => 'Lizenznehmer 2'),
        );

        $this->dummyData['licensees'] = array();

        foreach ($licensees as $licenseeData) {
            $licensee = $repository->findOneByName($licenseeData['name']);

            if (is_null($licensee)) {
                $licensee = new Licensee();
                $licensee->setName($licenseeData['name']);
                $dm->persist($licensee);
            }
            $this->dummyData['licensees'][] = $licensee;
        }

    }

    private function createUsers(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $users = array(
            array(
                'email' => 'reader@dembelo.tld',
                'password' => 'dembelo',
                'roles' => array('ROLE_USER'),
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => array('created' => time(), 'updated' => time()),
            ),
            array(
                'email' => 'licensee@dembelo.tld',
                'password' => 'dembelo',
                'roles' => array('ROLE_LICENSEE'),
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => array('created' => time(), 'updated' => time()),
            ),
        );

        $this->installUsers($users, $mongo, $dm);

    }

    private function installUsers(array $users, ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:User');

        $encoder = $this->getContainer()->get('security.password_encoder');

        if (!isset($this->dummyData['users'])) {
            $this->dummyData['users'] = array();
        }

        foreach ($users as $userData) {
            $user = $repository->findOneByEmail($userData['email']);

            if (is_null($user)) {
                $user = new User();
                $user->setEmail($userData['email']);
                $password = $encoder->encodePassword($user, $userData['password']);
                $user->setPassword($password);
                $user->setRoles($userData['roles']);
                $user->setGender($userData['gender']);
                $user->setSource($userData['source']);
                $user->setReason($userData['reason']);
                $user->setStatus($userData['status']);
                $user->setMetadata($userData['metadata']);

                if (in_array('ROLE_LICENSEE', $userData['roles'])) {
                    $user->setLicenseeId($this->dummyData['licensees'][0]->getId());
                }

                $dm->persist($user);
            }

            $this->dummyData['users'][] = $user;
        }
    }

    private function createTopics(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Topic');

        $this->dummyData['topics'] = array();

        $topicData = array(
            array('name' => 'Themenfeld 2', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 3', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 4', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 5', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 6', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 7', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 8', 'status' => Topic::STATUS_ACTIVE),
            array('name' => 'Themenfeld 9', 'status' => Topic::STATUS_ACTIVE),
        );

        foreach ($topicData as $topicDatum) {
            $topic = $repository->findOneByName($topicDatum['name']);
            if (is_null($topic)) {
                $topic = new Topic();
                $topic->setName($topicDatum['name']);
                $topic->setStatus($topicDatum['status']);
                $dm->persist($topic);
            }
            $this->dummyData['topics'][] = $topic;
        }

    }

    private function createTextnodes(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $loremIpsumLength = 3500;

        $repository = $mongo->getRepository('DembeloMain:Textnode');

        $allAccessNodes = $repository->findByAccess(true);
        if (count($allAccessNodes) >= 7) {
            return;
        }

        $loremIpsum = $this->getContainer()->get('apoutchika.lorem_ipsum');

        $textnodeData = array(
            array(
                'topic' => $this->dummyData['topics'][0],
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => true,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 1',
                    'Autor' => 'Autor 1',
                    'Verlag' => 'Verlag 1',
                ),
            ),
            array(
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 2',
                    'Autor' => 'Autor 2',
                    'Verlag' => 'Verlag 2',
                ),
            ),
            array(
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 3',
                    'Autor' => 'Autor 3',
                    'Verlag' => 'Verlag 3',
                ),
            ),
            array(
                'topic' => $this->dummyData['topics'][1],
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => true,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 4',
                    'Autor' => 'Autor 4',
                    'Verlag' => 'Verlag 4',
                ),
            ),
            array(
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 5',
                    'Autor' => 'Autor 5',
                    'Verlag' => 'Verlag 5',
                ),
            ),
            array(
                'text' => $loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => array(
                    'Titel' => 'Titel 6',
                    'Autor' => 'Autor 6',
                    'Verlag' => 'Verlag 6',
                ),
            ),
        );

        foreach ($textnodeData as $textnodeDatum) {
            $textnode = new Textnode();
            $textnode->setStatus(Textnode::STATUS_ACTIVE);
            if (isset($textnodeDatum['topic'])) {
                $textnode->setTopicId($textnodeDatum['topic']->getId());
            }
            if (isset($textnodeDatum['licensee'])) {
                $textnode->setLicenseeId($textnodeDatum['licensee']->getId());
            }
            $textnode->setCreated(date('Y-m-d H:i:s'));
            $textnode->setText($textnodeDatum['text']);
            $textnode->setAccess($textnodeDatum['access']);
            $textnode->setMetadata($textnodeDatum['metadata']);
            $dm->persist($textnode);
            $this->dummyData['textnodes'][] = $textnode;
        }
    }

    private function createHitches(ManagerRegistry $mongo, DocumentManager $dm)
    {
        if (isset($this->dummyData['textnodes']) !== true) {
            return;
        }

        if (count($this->dummyData['textnodes']) < 3) {
            return;
        }

        if ($this->dummyData['textnodes'][0]->getHitchCount() >= 2) {
            return;
        }

        $hitch = array();
        $hitch['textnodeId'] = $this->dummyData['textnodes'][1]->getId();
        $hitch['description'] = "Mehr Lorem.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->dummyData['textnodes'][0]->appendHitch($hitch);
        $dm->persist($this->dummyData['textnodes'][0]);

        $hitch = array();
        $hitch['textnodeId'] = $this->dummyData['textnodes'][2]->getId();
        $hitch['description'] = "Mehr Ipsum.";
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $this->dummyData['textnodes'][0]->appendHitch($hitch);
        $dm->persist($this->dummyData['textnodes'][0]);
    }
}
