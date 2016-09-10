<?php

/* Copyright (C) 2016 Michael Giesler
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

namespace AdminBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AdminBundle\Command\ImportCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ImportCommandTest
 */
class ImportCommandTest extends KernelTestCase
{
    /**
     * tests the execute method
     */
    public function testExecute()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new ImportCommand());

        $mockContainer = null;

        $command = $application->find('dembelo:import');
        $command->setContainer($mockContainer);
        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command'  => $command->getName(),
            'twine-archive-file' => 'somefile.html',
            '--licensee-name' => 'somelicensee',
            '--metadata-author' => 'someauthor',
            '--metadata-publisher' => 'somepublisher'

        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Username: Wouter', $output);

    }
}
