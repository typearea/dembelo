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

namespace DembeloMain\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FeatureToggle
 */
class FeatureToggle implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var bool[]
     */
    private $features = [
        'test_feature'  => false, // for unittesting, don't remove
        'login_enabled' => false, // registration and login enabled in navigation?
        'login_needed'  => false, //
        'paywall'       => true,
    ];

    /**
     * checks availability of a feature
     * @param string $featureKey
     *
     * @return bool
     */
    public function hasFeature(string $featureKey)
    {
        if (!isset($this->features[$featureKey])) {
            return false;
        }

        $defaultValue = $this->features[$featureKey];

        $parameterName = $this->buildParameterName($featureKey);
        if (!$this->container->hasParameter($parameterName)) {
            return $defaultValue;
        }
        $parameterValue = $this->container->getParameter($parameterName);

        return $parameterValue;
    }

    /**
     * returns an array of existing features
     * @return array
     */
    public function getFeatures(): array
    {
        return array_keys($this->features);
    }

    /**
     * @param string $featureKey
     *
     * @return string
     */
    private function buildParameterName(string $featureKey): string
    {
        return 'features.'.$featureKey;
    }
}
