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

    public function setUp()
    {
        $this->containerMock = $this->getMockBuilder(Container::class)->setMethods(['hasParameter', 'getParameter'])->getMock();
        $this->featureToggle = new FeatureToggle();
        $this->featureToggle->setContainer($this->containerMock);
    }

    public function testHasFeatureExists()
    {
        $this->assertTrue(method_exists($this->featureToggle, 'hasFeature'));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testHasFeatureThrowsErrorWithMissingParameter()
    {
        $this->featureToggle->hasFeature();
    }

    public function testHasFeatureReturnsFalseForUnknownFeature()
    {
        $this->assertFalse($this->featureToggle->hasFeature('unknownFeature'));
    }

    public function testHasFeatureReturnsFalseForTestFeature()
    {
        $this->assertFalse($this->featureToggle->hasFeature('test_feature'));
    }

    public function testHasFeatureReturnsWhenNoParameterExistsForTestFeature()
    {
        $this->containerMock->expects($this->atLeastOnce())
            ->method('hasParameter')
            ->with('features.test_feature')
            ->willReturn(false);
        $this->assertFalse($this->featureToggle->hasFeature('test_feature'));
    }

    public function testHasFeatureReturnsWhenTestFeatureIsEnabledByParameter()
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

    public function testGetFeaturesExists()
    {
        $this->assertTrue(method_exists($this->featureToggle, 'getFeatures'));
    }

    public function testGetFeaturesReturnsAnArray()
    {
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $this->featureToggle->getFeatures());
    }

    public function testGetFeaturesCountGreaterOne()
    {
        $this->assertGreaterThan(0, count($this->featureToggle->getFeatures()));
    }

    public function testGetFeaturesContainsTestFeature()
    {
        $features = $this->featureToggle->getFeatures();
        $this->assertContains('test_feature', $features);
    }
}
