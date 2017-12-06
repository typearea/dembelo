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
namespace DembeloMain\Twig;

use Twig_Extension;
use Twig_Function;

/**
 * Class VersionExtension
 * This twig extensions adds a function "version()" to twig that reads the dembelo version number
 */
class VersionExtension extends Twig_Extension
{
    /**
     * @var string
     */
    private $versionFile;

    /**
     * @param string $versionFile
     */
    public function __construct(string $versionFile)
    {
        $this->versionFile = $versionFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return array(
            new Twig_Function('dembeloversion', array($this, 'version')),
        );
    }

    /**
     * reads the version from filesystem and returns it
     * @return string
     */
    public function version(): string
    {
        return file_get_contents($this->versionFile);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'dembelo_version';
    }
}
