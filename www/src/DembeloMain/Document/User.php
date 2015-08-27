<?php

/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
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

namespace DembeloMain\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 *
 * @MongoDB\Document
 * @MongoDBUnique(fields="email")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @MongoDB\String
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @MongoDB\Collection
     * @Assert\NotBlank()
     */
    protected $roles;

    /**
     * @MongoDB\Collection
     */
    protected $currentTextnodes;

    /**
     * gets the mongodb id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * sets the mongoDB id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * gets the email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * sets the usermail, used for security
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * sets the email used as username
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * gets the password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * sets the password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * from UserInterface, not needed for our encoder
     *
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * gets the user's roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * sets the roles
     *
     * @param array $roles
     */
    public function setRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }
        $this->roles = $roles;
    }

    /**
     * @brief Gets the last textnode ID of topic \p $themeId the user was reading.
     * @param string $themeId Theme (= topic) ID.
     * @retval null There wasn't a textnode ID set for topic \p $themeId yet.
     * @return Textnode ID.
     */
    public function getCurrentTextnode($themeId)
    {
        if (isset($this->currentTextnodes[$themeId]) === true) {
            return $this->currentTextnodes[$themeId];
        }

        return null;
    }

    /**
     * @brief Sets the textnode ID for topic \p $themeID the user is
     *     currently reading.
     * @param string $themeId    Theme ID.
     * @param string $textnodeId ID of the textnode the user is
     *     currently reading.
     */
    public function setCurrentTextnode($themeId, $textnodeId)
    {
        $this->currentTextnodes[$themeId] = $textnodeId;
    }

    /**
     * from UserInterface
     */
    public function eraseCredentials()
    {
    }

    /**
     * serializes the object
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
            $this->currentTextnodes,
        ));
    }

    /**
     * unserializes the object
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
            $this->currentTextnodes
            ) = unserialize($serialized);
    }
}
