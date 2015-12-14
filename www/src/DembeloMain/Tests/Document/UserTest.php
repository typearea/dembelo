<?php

/* Copyright (C) 2015 Michael Giesler
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
 * @package DembeloMain
 */

namespace DembeloMain\Tests\Document;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Document\User;

/**
 * Class DocumentUserTest
 */
class UserTest extends WebTestCase
{
    /**
     * @var DembeloMain\Document\User
     */
    private $user;

    /**
     * setUp method
     */
    public function setUp()
    {
        $this->user = new User();
    }

    /**
     * a generic test for all class properties
     */
    public function testGenericMethods()
    {
        $properties = array('id', 'email', 'licenseeId', 'currentTextnode', 'gender', 'source', 'reason', 'password', 'status', 'activationHash');

        foreach ($properties as $property) {
            $getter = 'get'.ucfirst($property);
            $setter = 'set'.ucfirst($property);
            $string = tempnam($property, 'hrz');
            $this->assertNull($this->user->$getter());
            $this->user->$setter($string);
            $this->assertEquals($string, $this->user->$getter());
        }
    }

    /**
     * tests getUsername()
     */
    public function testGetUsernameShouldEqualEmail()
    {
        $this->user->setEmail('test');
        $this->assertEquals('test', $this->user->getUsername());
    }

    /**
     * tests getSalt()
     */
    public function testGetSaltShouldBeNull()
    {
        $this->assertNull($this->user->getSalt());
    }

    /**
     * tests setRoles()
     */
    public function testGetRolesShouldEqualSetRoles()
    {
        $this->user->setRoles(array('test'));
        $this->assertEquals(array('test'), $this->user->getRoles());
    }

    /**
     * tests setRoles() with a non array as argument
     */
    public function testGetRolesShouldBeArray()
    {
        $this->user->setRoles('test');
        $this->assertEquals(array('test'), $this->user->getRoles());
    }

    /**
     * tests serialize()
     */
    public function testSerialize()
    {
        $this->user->setId('id');
        $this->user->setEmail('email');
        $this->user->setPassword('pw');
        $this->user->setCurrentTextnode('55cd2a9808985ca80c3c9877');
        $this->assertEquals(serialize(array('id', 'email', 'pw', '55cd2a9808985ca80c3c9877')), $this->user->serialize());
    }

    /**
     * tests unserialize()
     */
    public function testUnserialize()
    {
        $serialized = serialize(array('id', 'email', 'pw', '55cd2a9808985ca80c3c9877'));
        $this->user->unserialize($serialized);
        $this->assertEquals('id', $this->user->getId());
        $this->assertEquals('email', $this->user->getEmail());
        $this->assertEquals('pw', $this->user->getPassword());
        $this->assertEquals('55cd2a9808985ca80c3c9877', $this->user->getCurrentTextnode());
    }

    /**
     * tests eraseCredentials()
     */
    public function testEraseCredentials()
    {
        $this->assertNull($this->user->eraseCredentials());
    }

    /**
     * tests isAccountNonExpired
     */
    public function testIsAccountNonExpired()
    {
        $this->assertTrue($this->user->isAccountNonExpired());
    }

    /**
     * tests isAccountNonLocked
     */
    public function testIsAccountNonLocked()
    {
        $this->assertTrue($this->user->isAccountNonLocked());
    }

    /**
     * tests isCredentialsNonExpired
     */
    public function testIsCredentialsNonExpired()
    {
        $this->assertTrue($this->user->isCredentialsNonExpired());
    }

    /**
     * tests isEnabled for not set status
     */
    public function testIsEnabledDefault()
    {
        $this->assertFalse($this->user->isEnabled());
    }

    /**
     * tests isEnabled for status = 0
     */
    public function testIsEnabledForStatusZero()
    {
        $this->user->setStatus(0);
        $this->assertFalse($this->user->isEnabled());
    }

    /**
     * tests isEnabled for status = 1
     */
    public function testIsEnabledForStatusOne()
    {
        $this->user->setStatus(1);
        $this->assertTrue($this->user->isEnabled());
    }
}
