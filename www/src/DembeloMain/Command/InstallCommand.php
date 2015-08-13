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
 * @package DembeloMain
 */

namespace DembeloMain\Command;


use DembeloMain\Document\Textnode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DembeloMain\Document\User;
use DembeloMain\Document\Author;
use DembeloMain\Document\Topic;
use DembeloMain\Document\Story;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;

class InstallCommand extends ContainerAwareCommand
{

    private $dummyData = array();

    protected function configure()
    {
        $this
            ->setName('dembelo:install')
            ->setDescription('Installation Routine');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $mongo = $this->getContainer()->get('doctrine_mongodb');

        $dm = $mongo->getManager();

        $this->createUsers($mongo, $dm);
        $output->writeln("Users installed...");

        $this->createAuthors($mongo, $dm);
        $output->writeln("Authors installed...");

        $this->createTopics($mongo, $dm);
        $output->writeln("Topics installed...");

        $this->createStories($mongo, $dm);
        $output->writeln("Stories installed...");

        $this->createTextnodes($mongo, $dm);
        $output->writeln("Textnodes installed...");

        $dm->flush();

        $output->writeln("<info>Installation Done</info>");
    }

    private function createUsers(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:User');

        $encoder = $this->getContainer()->get('security.password_encoder');

        $user = $repository->findOneByEmail('admin@dembelo.tld');

        $this->dummyData['users'] = array();

        if (is_null($user)) {
            $user = new User();
            $user->setEmail('admin@dembelo.tld');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $user->setRoles(array("ROLE_ADMIN"));
            $dm->persist($user);
        }
        $this->dummyData['users'][] = $user;

        $user = $repository->findOneByEmail('reader@dembelo.tld');

        if (is_null($user)) {
            $user = new User();
            $user->setEmail('reader@dembelo.tld');
            $password = $encoder->encodePassword($user, 'dembelo');
            $user->setPassword($password);
            $user->setRoles(array("ROLE_USER"));
            $dm->persist($user);
        }
        $this->dummyData['users'][] = $user;
    }

    private function createAuthors(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Author');

        $author = $repository->findOneByName('Alfons Autor');

        $this->dummyData['authors'] = array();

        if (is_null($author)) {
            $author = new Author();
            $author->setName('Alfons Autor');
            $author->setStatus(Author::STATUS_ACTIVE);
            $dm->persist($author);
        }
        $this->dummyData['authors'][] = $author;
    }

    private function createTopics(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Topic');

        $this->dummyData['topics'] = array();

        $topic = $repository->findOneByName('Lorem');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Lorem');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 2');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 2');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 3');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 3');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 4');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 4');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 5');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 5');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 6');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 6');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 7');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 7');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 8');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 8');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;

        $topic = $repository->findOneByName('Thema 9');
        if (is_null($topic)) {
            $topic = new Topic();
            $topic->setName('Thema 9');
            $topic->setStatus(Topic::STATUS_ACTIVE);
            $dm->persist($topic);
        }
        $this->dummyData['topics'][] = $topic;
    }

    private function createStories(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Story');

        $this->dummyData['stories'] = array();

        $topicId = $this->dummyData['topics'][0]->getId();

        $story = $repository->findOneByName('Ipsum');
        if (is_null($story)) {
            $story = new Story();
            $story->setName('Ipsum');
            $story->setStatus(Story::STATUS_ACTIVE);
            $story->setTopicId($topicId);
            $dm->persist($story);
        }
        $this->dummyData['stories'][] = $story;

        $story = $repository->findOneByName('Ipsum II');
        if (is_null($story)) {
            $story = new Story();
            $story->setName('Ipsum II');
            $story->setStatus(Story::STATUS_ACTIVE);
            $story->setTopicId($topicId);
            $dm->persist($story);
        }
        $this->dummyData['stories'][] = $story;
    }

    private function createTextnodes(ManagerRegistry $mongo, DocumentManager $dm)
    {
        $repository = $mongo->getRepository('DembeloMain:Textnode');

        $allIntroductions = $repository->findByType(Textnode::TYPE_INTRODUCTION);
        if (count($allIntroductions) >= 2) {
            return;
        }

        $loremIpsum = $this->getContainer()->get('apoutchika.lorem_ipsum');

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][0]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_INTRODUCTION);
        $dm->persist($textnode);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][0]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_DEEPENING);
        $dm->persist($textnode);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][0]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_DEEPENING);
        $dm->persist($textnode);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][1]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_INTRODUCTION);
        $dm->persist($textnode);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][1]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_DEEPENING);
        $dm->persist($textnode);

        $textnode = new Textnode();
        $textnode->setStatus(Textnode::STATUS_ACTIVE);
        $textnode->setTopicId($this->dummyData['topics'][0]->getId());
        $textnode->setAuthorId($this->dummyData['authors'][0]->getId());
        $textnode->setCreated(date('Y-m-d H:i:s'));
        $textnode->setStoryId($this->dummyData['stories'][1]->getId());
        $textnode->setText($loremIpsum->getWords (3500));
        $textnode->setType(Textnode::TYPE_DEEPENING);
        $dm->persist($textnode);
    }
}
