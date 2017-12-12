<?php
/* Copyright (C) 2015-2017 Michael Giesler, Stephan Kreutzer
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
namespace DembeloMain\Command;

use Apoutchika\LoremIpsumBundle\Services\LoremIpsum;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DembeloMain\Document\User;
use DembeloMain\Document\Topic;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Console\Input\InputOption;
use DembeloMain\Document\Readpath;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class InstallCommand
 */
class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'dembelo:install';

    /**
     * @var ManagerRegistry
     */
    private $mongo;

    /**
     * @var array
     */
    private $dummyData = [];

    /**
     * @var string
     */
    private $topicImageDirectory;

    /**
     * @var TopicRepositoryInterface
     */
    private $topicRepository;

    /**
     * @var TextNodeRepositoryInterface
     */
    private $textNodeRepository;

    /**
     * @var LicenseeRepositoryInterface
     */
    private $licenseeRepository;

    /**
     * @var string
     */
    private $topicDummyImageDirectory;

    /**
     * @var LoremIpsum
     */
    private $loremIpsum;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * InstallCommand constructor.
     *
     * @param ManagerRegistry             $mongo
     * @param TopicRepositoryInterface    $topicRepository
     * @param TextNodeRepositoryInterface $textNodeRepository
     * @param LicenseeRepositoryInterface $licenseeRepository
     * @param UserRepositoryInterface     $userRepository
     * @param LoremIpsum                  $loremIpsum
     * @param UserPasswordEncoder         $passwordEncoder
     * @param string                      $topicDummyImageDirectory
     * @param string                      $topicImageDirectory
     */
    public function __construct(ManagerRegistry $mongo, TopicRepositoryInterface $topicRepository, TextNodeRepositoryInterface $textNodeRepository, LicenseeRepositoryInterface $licenseeRepository, UserRepositoryInterface $userRepository, LoremIpsum $loremIpsum, UserPasswordEncoder $passwordEncoder, string $topicDummyImageDirectory, string $topicImageDirectory)
    {
        $this->mongo = $mongo;
        $this->topicImageDirectory = $topicImageDirectory;
        $this->topicRepository = $topicRepository;
        $this->textNodeRepository = $textNodeRepository;
        $this->licenseeRepository = $licenseeRepository;
        $this->topicDummyImageDirectory = $topicDummyImageDirectory;
        $this->loremIpsum = $loremIpsum;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('purge-db')) {
            $this->purgeDB();
            $output->writeln('<info>Database cleared</info>');
            $this->cleanImageDirectories();
            $output->writeln('<info>Directories cleared</info>');
        }

        $this->installDefaultUsers($output);
        $output->writeln('<info>Default users installed</info>');

        if ($input->getOption('with-dummy-data')) {
            $this->installDummyData($output);
            $output->writeln('<info>Dummy data installed</info>');
        }
    }

    /**
     * @return void
     */
    protected function purgeDB(): void
    {
        $collectionClasses = [
            Licensee::class,
            Readpath::class,
            Textnode::class,
            Topic::class,
            User::class,
        ];

        $dm = $this->mongo->getManager();

        foreach ($collectionClasses as $collectionClass) {
            $collection = $dm->getDocumentCollection($collectionClass);
            $collection->remove([]);
        }
    }

    /**
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function installDefaultUsers(OutputInterface $output): void
    {
        $this->installAdminUser();
        $output->writeln('admin user installed');
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function installAdminUser(): void
    {
        $users = [
            [
                'email' => 'admin@dembelo.tld',
                'password' => 'dembelo',
                'roles' => ['ROLE_ADMIN'],
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => ['created' => time(), 'updated' => time()],
            ],
        ];

        $this->installUsers($users);
    }

    /**
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function installDummyData(OutputInterface $output): void
    {
        $this->createLicensees();
        $output->writeln('Licensees installed...');

        $this->createUsers();
        $output->writeln('Users installed...');

        $this->createTopics();
        $output->writeln('Topics installed...');

        $this->createTextnodes();
        $output->writeln('Textnodes installed...');

        $this->createHitches();
        $output->writeln('Hitches installed...');
    }

    /**
     * @return void
     */
    private function createLicensees(): void
    {
        $licensees = [
            ['name' => 'Lizenznehmer 1'],
            ['name' => 'Lizenznehmer 2'],
        ];

        $this->dummyData['licensees'] = [];

        foreach ($licensees as $licenseeData) {
            $licensee = $this->licenseeRepository->findOneByName($licenseeData['name']);

            if (null === $licensee) {
                $licensee = new Licensee();
                $licensee->setName($licenseeData['name']);
                $this->licenseeRepository->save($licensee);
            }
            $this->dummyData['licensees'][] = $licensee;
        }
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    private function createUsers(): void
    {
        $users = [
            [
                'email' => 'reader@dembelo.tld',
                'password' => 'dembelo',
                'roles' => ['ROLE_USER'],
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => ['created' => time(), 'updated' => time()],
            ],
            [
                'email' => 'licensee@dembelo.tld',
                'password' => 'dembelo',
                'roles' => ['ROLE_LICENSEE'],
                'gender' => 'm',
                'status' => 1,
                'source' => '',
                'reason' => '',
                'metadata' => ['created' => time(), 'updated' => time()],
            ],
        ];

        $this->installUsers($users);
    }

    /**
     * @param array $users
     *
     * @return void
     *
     * @throws \Exception
     */
    private function installUsers(array $users): void
    {
        if (!isset($this->dummyData['users'])) {
            $this->dummyData['users'] = array();
        }

        foreach ($users as $userData) {
            $user = $this->userRepository->findOneBy(['email' => $userData['email']]);

            if (null === $user) {
                $user = new User();
                $user->setEmail($userData['email']);
                $password = $this->passwordEncoder->encodePassword($user, $userData['password']);
                $user->setPassword($password);
                $user->setRoles($userData['roles']);
                $user->setGender($userData['gender']);
                $user->setSource($userData['source']);
                $user->setReason($userData['reason']);
                $user->setStatus($userData['status']);
                $user->setMetadata($userData['metadata']);

                if (\in_array('ROLE_LICENSEE', $userData['roles'], true)) {
                    $user->setLicenseeId($this->dummyData['licensees'][0]->getId());
                }

                $this->userRepository->save($user);
            }

            $this->dummyData['users'][] = $user;
        }
    }

    /**
     * @return void
     *
     * @throws \RuntimeException
     */
    private function createTopics(): void
    {
        $this->dummyData['topics'] = [];

        $topicData = [
            ['name' => 'Themenfeld 2', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 3', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 4', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 5', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 6', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 7', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 8', 'status' => Topic::STATUS_ACTIVE],
            ['name' => 'Themenfeld 9', 'status' => Topic::STATUS_ACTIVE],
        ];

        $imagesSrcFolder = $this->topicDummyImageDirectory;
        $imagesTargetFolder = $this->topicImageDirectory;

        $sortKey = 1;
        foreach ($topicData as $topicDatum) {
            $topic = $this->topicRepository->findOneByName($topicDatum['name']);
            if (null === $topic) {
                $imagename = 'bg0'.$sortKey.'.jpg';
                $topic = new Topic();
                $topic->setName($topicDatum['name']);
                $topic->setStatus($topicDatum['status']);
                $topic->setSortKey($sortKey);
                $topic->setOriginalImageName($imagename);
                $topic->setImageFilename($imagename);
                $this->topicRepository->save($topic);
                $topicFolder = $imagesTargetFolder.'/'.$topic->getId().'/';
                if (!mkdir($topicFolder) && !is_dir($topicFolder)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $topicFolder));
                }
                copy($imagesSrcFolder.$imagename, $topicFolder.'/'.$imagename);
                ++$sortKey;
            }
            $this->dummyData['topics'][] = $topic;
        }
    }

    /**
     * @return void
     */
    private function createTextnodes(): void
    {
        $loremIpsumLength = 3500;

        $allAccessNodes = $this->textNodeRepository->findBy(['access' => true]);
        if (count($allAccessNodes) >= 7) {
            return;
        }

        $textnodeData = [
            [
                'topic' => $this->dummyData['topics'][0],
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => true,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 1',
                    'Autor' => 'Autor 1',
                    'Verlag' => 'Verlag 1',
                ],
            ],
            [
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 2',
                    'Autor' => 'Autor 2',
                    'Verlag' => 'Verlag 2',
                ],
            ],
            [
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 3',
                    'Autor' => 'Autor 3',
                    'Verlag' => 'Verlag 3',
                ],
            ],
            [
                'topic' => $this->dummyData['topics'][1],
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => true,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 4',
                    'Autor' => 'Autor 4',
                    'Verlag' => 'Verlag 4',
                ],
            ],
            [
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 5',
                    'Autor' => 'Autor 5',
                    'Verlag' => 'Verlag 5',
                ],
            ],
            [
                'text' => $this->loremIpsum->getWords($loremIpsumLength),
                'access' => false,
                'licensee' => $this->dummyData['licensees'][0],
                'metadata' => [
                    'Titel' => 'Titel 6',
                    'Autor' => 'Autor 6',
                    'Verlag' => 'Verlag 6',
                ],
            ],
        ];

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
            $this->textNodeRepository->save($textnode);

            $this->dummyData['textnodes'][] = $textnode;
        }
    }

    /**
     * @return void
     */
    private function createHitches(): void
    {
        if (isset($this->dummyData['textnodes']) !== true) {
            return;
        }

        /* @var $dummyTextnodes Textnode[] */
        $dummyTextnodes = $this->dummyData['textnodes'];

        if (count($dummyTextnodes) < 3) {
            return;
        }

        if ($dummyTextnodes[0]->getHitchCount() >= 2) {
            return;
        }

        $hitch = [];
        $hitch['textnodeId'] = $dummyTextnodes[1]->getId();
        $hitch['description'] = 'Mehr Lorem.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $dummyTextnodes[0]->appendHitch($hitch);
        $this->textNodeRepository->save($this->dummyData['textnodes'][0]);

        $hitch = [];
        $hitch['textnodeId'] = $dummyTextnodes[2]->getId();
        $hitch['description'] = 'Mehr Ipsum.';
        $hitch['status'] = Textnode::HITCH_STATUS_ACTIVE;
        $dummyTextnodes[0]->appendHitch($hitch);

        $this->textNodeRepository->save($dummyTextnodes[0]);
    }

    /**
     * @return void
     */
    private function cleanImageDirectories(): void
    {
        $topicImageDirectory = $this->topicImageDirectory.'/';
        if (is_dir($topicImageDirectory)) {
            shell_exec('rm -r '.$topicImageDirectory.'*');
        }
    }
}
