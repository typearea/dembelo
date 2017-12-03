<?php
/* Copyright (C) 2017 Michael Giesler
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
use Twig_SimpleFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VersionExtension
 * This twig extensions adds a function "version()" to twig that reads the dembelo version number
 */
class FeatureToggleExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('has_feature', array($this, 'hasFeature')),
        );
    }

    /**
     * checks the featureToggle Service for feature existance
     * @param string $featureKey name of feature
     *
     * @return bool
     */
    public function hasFeature($featureKey)
    {
        return $this->container->get('app.feature_toggle')->hasFeature($featureKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'has_feature';
    }
}
