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

namespace DembeloMain\Tests\Model;

use DembeloMain\Model\FeatureToggle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class FeatureToggleTest
 */
class FeatureToggleTest extends WebTestCase
{
    /**
     * @var FeatureToggle
     */
    private $featureToggle;

    /**
     * @return void
     */
    public function testHasFeatureExists(): void
    {
        $featureToggle = new FeatureToggle([]);
        $this->assertTrue(method_exists($featureToggle, 'hasFeature'));
    }

    /**
     * @return void
     */
    public function testHasFeatureThrowsErrorWithMissingParameter(): void
    {
        $featureToggle = new FeatureToggle([]);

        $this->expectException(\ArgumentCountError::class);
        $featureToggle->hasFeature();
    }

    /**
     * tests hasFeature() returns false for unknown feature
     */
    public function testHasFeatureReturnsFalseForUnknownFeature()
    {
        $featureToggle = new FeatureToggle([]);
        self::assertFalse($featureToggle->hasFeature('unknownFeature'));
    }

    /**
     * tests hasFeature returns false for test feature
     */
    public function testHasFeatureReturnsFalseForTestFeature()
    {
        $featureToggle = new FeatureToggle([]);
        self::assertFalse($featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests hasFeature() returns false when no parameter exists for test feature
     */
    public function testHasFeatureReturnsWhenNoParameterExistsForTestFeature()
    {
        $featureToggle = new FeatureToggle(
            [
                [
                    'test_feature' => false,
                ],
            ]
        );
        self::assertFalse($featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests hasFeature() returns true when test Feature is enabled by parameter
     */
    public function testHasFeatureReturnsTrueWhenTestFeatureIsEnabledByParameter()
    {
        $featureToggle = new FeatureToggle([
            ['test_feature' => true],
        ]);

        self::assertTrue($featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests getFeatures() is existing
     */
    public function testGetFeaturesExists()
    {
        $featureToggle = new FeatureToggle([]);
        self::assertTrue(method_exists($featureToggle, 'getFeatures'));
    }

    /**
     * tests getFeatures() returns an array
     */
    public function testGetFeaturesReturnsAnArray()
    {
        $featureToggle = new FeatureToggle([]);
        self::assertInternalType('array', $featureToggle->getFeatures());
    }

    /**
     * tests getFeature() count is greater than 1
     */
    public function testGetFeaturesCountGreaterOne()
    {
        $featureToggle = new FeatureToggle([]);
        self::assertGreaterThan(0, count($featureToggle->getFeatures()));
    }

    /**
     * tests getFeatures() contains the test feature
     */
    public function testGetFeaturesContainsTestFeature()
    {
        $featureToggle = new FeatureToggle([]);
        $features = $featureToggle->getFeatures();
        self::assertContains('test_feature', $features);
    }
}
