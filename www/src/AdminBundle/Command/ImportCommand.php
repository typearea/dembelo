<?php
/* Copyright (C) 2015, 2016 Stephan Kreutzer, Michael Giesler
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
namespace AdminBundle\Command;

use DembeloMain\Document\Importfile;
use DembeloMain\Model\Repository\LicenseeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class ImportCommand
 */
class ImportCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $twineArchivePath;

    /**
     * @var OutputInterface
     */
    private $output = null;

    /**
     * @var misc
     */
    private $mongo = null;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm = null;

    /**
     * @var string|null
     */
    private $licenseeId = null;

    /**
     * @var string|null
     */
    private $topicId = null;

    /**
     * @var string
     */
    private $author = '';

    /**
     * @var string
     */
    private $publisher = '';

    /**
     * configures the symfony cli command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dembelo:import')
            ->setDescription('Twine Archive Import')
            ->addArgument(
                'twine-archive-file',
                InputArgument::REQUIRED,
                'The path of the Twine archive file.'
            )
            ->addOption(
                'licensee-name',
                'l',
                InputOption::VALUE_REQUIRED,
                'The name of the licensee to which the imported textnodes belong to.'
            )
            ->addOption(
                'topic-name',
                't',
                InputOption::VALUE_REQUIRED,
                'The name of the topic to which the imported textnodes belong to.'
            )
            ->addOption(
                'metadata-author',
                'a',
                InputOption::VALUE_REQUIRED,
                'The author of all the stories in the Twine archive file (will end up as metadata).'
            )
            ->addOption(
                'metadata-publisher',
                'p',
                InputOption::VALUE_REQUIRED,
                'The publisher of all the stories in the Twine archive file (will end up as metadata).'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importTwine = $this->getContainer()->get('admin.import.twine');
        $this->output = $output;
        $this->prepare($input);

        if (file_exists($this->twineArchivePath) !== true) {
            $this->output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' doesn't exist.</error>");

            return -1;
        }

        if (is_readable($this->twineArchivePath) !== true) {
            $this->output->writeln("<error>Parameter 'twine-archive-file': File '".$this->twineArchivePath."' isn't readable.</error>");

            return -1;
        }

        try {
            $importfile = new Importfile();
            $importfile->setFilename($this->twineArchivePath);
            $importfile->setLicenseeId($this->licenseeId);
            $importfile->setAuthor($this->author);
            $importfile->setPublisher($this->publisher);
            $importfile->setTopicId($this->topicId);

            $this->dm->persist($importfile);
            $this->dm->flush();

            $importTwine->run($importfile);

            $this->dm->flush();
        } catch (\Exception $ex) {
            $output->writeln('<error>'.$ex->getMessage().'</error>');

            $importTwine->parserFree();

            return -1;
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    private function prepare(InputInterface $input)
    {
        $styleWarning = new OutputFormatterStyle('black', 'yellow');
        $this->output->getFormatter()->setStyle('warning', $styleWarning);

        $this->mongo = $this->getContainer()->get('doctrine_mongodb');
        $this->dm = $this->mongo->getManager();

        /**
         * @var $repositoryLicensee LicenseeRepositoryInterface
         */
        $repositoryLicensee = $this->mongo->getRepository('DembeloMain:Licensee');

        /**
         * @var $licensee \DembeloMain\Document\Licensee
         */
        $licensee = $repositoryLicensee->findOneByName($input->getOption('licensee-name'));
        if (null === $licensee) {
            throw new \Exception(sprintf("<error>A Licensee named '%s' doesn't exist.</error>", $input->getOption('licensee-name')));
        }

        /**
         * @var $repositoryTopic TopicRepositoryInterface
         */
        $repositoryTopic = $this->mongo->getRepository('DembeloMain:Topic');
        $topic = $repositoryTopic->findOneByName($input->getOption('topic-name'));
        if (null === $topic) {
            throw new \Exception(sprintf("<error>A Topic named '%s' doesn't exist.</error>", $input->getOption('topic-name')));
        }
        $this->topicId = $topic->getId();

        $this->author = $input->getOption('metadata-author');
        $this->publisher = $input->getOption('metadata-publisher');

        $this->licenseeId = $licensee->getId();

        $this->twineArchivePath = $input->getArgument('twine-archive-file');
    }
}
