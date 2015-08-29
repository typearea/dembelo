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

namespace DembeloMain\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Document\User;

/**
 * Class DefaultControllerTest
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
     * tests getId()
     */
    public function testGetIdShouldBeEqualSetId()
    {
        $this->user->setId('testid');
        $this->assertEquals('testid', $this->user->getId());
    }

    /**
     * tests getId()
     */
    public function testGetIdShouldBeNullWhenNotSet()
    {
        $this->assertNull($this->user->getId());
    }

    /**
     * tests getEmail
     */
    public function testGetEmailShouldBeEqualSetEmail()
    {
        $this->user->setEmail('test');
        $this->assertEquals('test', $this->user->getEmail());
    }

    /**
     * tests GetEmail when not set
     */
    public function testGetEmailShouldBeNullWhenNotSet()
    {
        $this->assertNull($this->user->getEmail());
    }

    /**
     * tests getUsername when not set
     */
    public function testGetUserNameShouldBeNullWhenNotSet()
    {
        $this->assertNull($this->user->getUsername());
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
     * tests getPassword()
     */
    public function testGetPasswordShouldBeEqualSetPassword()
    {
        $this->user->setPassword('test');
        $this->assertEquals('test', $this->user->getPassword());
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
        $this->assertEquals(serialize(array('id', 'email', 'pw')), $this->user->serialize());
    }

    /**
     * tests unserialize()
     */
    public function testUnserialize()
    {
        $serialized = serialize(array('id', 'email', 'pw'));
        $this->user->unserialize($serialized);
        $this->assertEquals('id', $this->user->getId());
        $this->assertEquals('email', $this->user->getEmail());
        $this->assertEquals('pw', $this->user->getPassword());
    }

    /**
     * tests eraseCredentials()
     */
    public function testEraseCredentials()
    {
        $this->assertNull($this->user->eraseCredentials());
    }
}
