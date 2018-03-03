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

/**
 * Class FeatureToggle
 */
class FeatureToggle
{
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
     * @var bool[]
     */
    private $featureParameters = [];

    /**
     * FeatureToggle constructor.
     * @param array $featureParameters
     */
    public function __construct(array $featureParameters)
    {
        foreach ($featureParameters as $featureParameter) {
            $this->featureParameters += $featureParameter;
        }
    }

    /**
     * checks availability of a feature
     * @param string $featureKey
     *
     * @return bool
     */
    public function hasFeature(string $featureKey): bool
    {
        if (!isset($this->features[$featureKey])) {
            return false;
        }

        $defaultValue = $this->features[$featureKey];

        if (!isset($this->featureParameters[$featureKey])) {
            return $defaultValue;
        }

        return $this->featureParameters[$featureKey];
    }

    /**
     * returns an array of existing features
     * @return string[]
     */
    public function getFeatures(): array
    {
        return array_keys($this->features);
    }
}
