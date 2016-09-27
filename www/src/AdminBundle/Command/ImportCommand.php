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


/**
 * @package AdminBundle
 */

namespace AdminBundle\Command;

use DembeloMain\Document\Importfile;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class ImportCommand
 * @package AdminBundle
 */
class ImportCommand extends ContainerAwareCommand
{
    private $twineArchivePath;

    /**
     * @var OutputInterface
     */
    private $output = null;
    private $mongo = null;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm = null;

    private $licenseeId = null;
    private $author = "";
    private $publisher = "";

    /**
     * configures the symfony cli command
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

    protected function execute(InputInterface $input, OutputInterface $output)
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

    private function prepare(InputInterface $input)
    {
        $styleWarning = new OutputFormatterStyle('black', 'yellow');
        $this->output->getFormatter()->setStyle('warning', $styleWarning);

        $this->mongo = $this->getContainer()->get('doctrine_mongodb');
        $this->dm = $this->mongo->getManager();

        /**
         * @var $repositoryLicensee EntityRepository
         */
        $repositoryLicensee = $this->mongo->getRepository('DembeloMain:Licensee');

        /**
         * @var $licensee \DembeloMain\Document\Licensee
         */
        $licensee = $repositoryLicensee->findOneByName($input->getOption('licensee-name'));
        if (is_null($licensee)) {
            throw new \Exception("<error>A Licensee named '".$input->getOption('licensee-name')."' doesn't exist.</error>");
        }

        $this->author = $input->getOption('metadata-author');
        $this->publisher = $input->getOption('metadata-publisher');

        $this->licenseeId = $licensee->getId();

        $this->twineArchivePath = $input->getArgument('twine-archive-file');
    }
}
