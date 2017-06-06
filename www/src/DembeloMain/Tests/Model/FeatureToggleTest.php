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
 * @package DembeloMain\Tests\Model
 */
class FeatureToggleTest extends WebTestCase
{
    /**
     * @var FeatureToggle
     */
    private $featureToggle;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->containerMock = $this->getMockBuilder(Container::class)->setMethods(['hasParameter', 'getParameter'])->getMock();
        $this->featureToggle = new FeatureToggle();
        $this->featureToggle->setContainer($this->containerMock);
    }

    /**
     * tests hasFeature() exists
     */
    public function testHasFeatureExists()
    {
        $this->assertTrue(method_exists($this->featureToggle, 'hasFeature'));
    }

    /**
     * tests hasFeature() throws an error because of missing parameter
     */
    public function testHasFeatureThrowsErrorWithMissingParameter()
    {
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            $this->expectException(\ArgumentCountError::class);
        } else {
            $this->expectException(\PHPUnit_Framework_Error::class);
        }
        $this->featureToggle->hasFeature();
    }

    /**
     * tests hasFeature() returns false for unknown feature
     */
    public function testHasFeatureReturnsFalseForUnknownFeature()
    {
        $this->assertFalse($this->featureToggle->hasFeature('unknownFeature'));
    }

    /**
     * tests hasFeature returns false for test feature
     */
    public function testHasFeatureReturnsFalseForTestFeature()
    {
        $this->assertFalse($this->featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests hasFeature() returns false when no parameter exists for test feature
     */
    public function testHasFeatureReturnsWhenNoParameterExistsForTestFeature()
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('hasParameter')
            ->with('features.test_feature')
            ->willReturn(false);
        $this->assertFalse($this->featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests hasFeature() returns true when test Feature is enabled by parameter
     */
    public function testHasFeatureReturnsTrueWhenTestFeatureIsEnabledByParameter()
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('hasParameter')
            ->with('features.test_feature')
            ->willReturn(true);

        $this->containerMock->expects($this->atLeastOnce())
            ->method('getParameter')
            ->with('features.test_feature')
            ->willReturn(true);

        $this->assertTrue($this->featureToggle->hasFeature('test_feature'));
    }

    /**
     * tests getFeatures() is existing
     */
    public function testGetFeaturesExists()
    {
        $this->assertTrue(method_exists($this->featureToggle, 'getFeatures'));
    }

    /**
     * tests getFeatures() returns an array
     */
    public function testGetFeaturesReturnsAnArray()
    {
        $this->assertInternalType('array', $this->featureToggle->getFeatures());
    }

    /**
     * tests getFeature() count is greater than 1
     */
    public function testGetFeaturesCountGreaterOne()
    {
        $this->assertGreaterThan(0, count($this->featureToggle->getFeatures()));
    }

    /**
     * tests getFeatures() contains the test feature
     */
    public function testGetFeaturesContainsTestFeature()
    {
        $features = $this->featureToggle->getFeatures();
        $this->assertContains('test_feature', $features);
    }
}
