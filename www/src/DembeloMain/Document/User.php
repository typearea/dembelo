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
namespace DembeloMain\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Exception;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 *
 * @MongoDB\Document
 *
 * @MongoDBUnique(fields="email")
 *
 * @MongoDB\Document(repositoryClass="\DembeloMain\Model\Repository\Doctrine\ODM\UserRepository")
 */
class User implements UserInterface, \Serializable, AdvancedUserInterface, EquatableInterface
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @MongoDB\Field(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @MongoDB\Field(type="collection")
     *
     * @Assert\NotBlank()
     */
    protected $roles;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $licenseeId;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $currentTextnode;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $gender;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $source;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $reason;

    /**
     * @MongoDB\Field(type="int")
     *
     * @Assert\NotBlank()
     */
    protected $status;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $activationHash;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $metadata;

    /**
     * @MongoDB\Field(type="object_id")
     */
    protected $lastTopicId;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $favorites = [];

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
     * @param array|string $roles
     */
    public function setRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }
        $this->roles = $roles;
    }

    /**
     * Gets the last textnode ID of topic \p $topicId the user was reading.
     *
     * @return string|null Textnode ID or null, if there wasn't a current
     *     textnode ID set so far.
     */
    public function getCurrentTextnode()
    {
        return $this->currentTextnode;
    }

    /**
     * Saves the ID of the textnode the user is currently
     *     reading.
     *
     * @param string $textnodeId ID of the textnode the user is
     *                           currently reading.
     */
    public function setCurrentTextnode($textnodeId)
    {
        $this->currentTextnode = $textnodeId;
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
            $this->currentTextnode,
        ));
    }

    /**
     * unserializes the object
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
            $this->currentTextnode,
        ] = unserialize($serialized);
    }

    /**
     * sets the licensee id
     *
     * @param string $id licensee ID
     */
    public function setLicenseeId($id)
    {
        $this->licenseeId = $id;
    }

    /**
     * gets the licensee id
     *
     * @return string
     */
    public function getLicenseeId()
    {
        return $this->licenseeId;
    }

    /**
     * sets the gender
     *
     * @param string $gender Gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * gets the gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * gets the source from where the user came to this site
     *
     * @return String
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * sets the source from where the user came to this site
     *
     * @param String $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * gets the reason for registration
     *
     * @return String
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * sets the reason for registration
     *
     * @param String $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * gets status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * sets status
     *
     * @param integer $status status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * gets activation hash
     *
     * @return mixed
     */
    public function getActivationHash()
    {
        return $this->activationHash;
    }

    /**
     * sets activation hash
     *
     * @param String $hash activation hash
     */
    public function setActivationHash($hash)
    {
        $this->activationHash = $hash;
    }

    /**
     * checks if account is not expired
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * checks if account is not locked
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * checks if credentials are not expired
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * checks if enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status === 1;
    }

    /**
     * gets the metadata
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * sets the metadata
     * @param array|string $metadata
     * @param string       $value
     *
     * @throws Exception
     */
    public function setMetadata($metadata, $value = null)
    {
        if (is_array($metadata) && null === $value) {
            $this->metadata = $metadata;
        } elseif (is_string($metadata) && null !== $value) {
            $this->metadata[$metadata] = $value;
        } else {
            throw new Exception('invalid data');
        }
    }

    /**
     * sets the last topic id this user selected
     *
     * @param string $lastTopicId
     */
    public function setLastTopicId($lastTopicId)
    {
        $this->lastTopicId = $lastTopicId;
    }

    /**
     * gets the last topic id this user selected
     * @return string
     */
    public function getLastTopicId()
    {
        return $this->lastTopicId;
    }

    /**
     * sets favorite textnode for topic
     * @param String $topicId
     * @param String $textnodeId
     */
    public function setFavorite($topicId, $textnodeId)
    {
        $this->favorites[$topicId] = $textnodeId;
    }

    /**
     * gets favorite textnode for topic
     * @param String $topicId
     *
     * @return null|String
     */
    public function getFavorite($topicId)
    {
        if (!isset($this->favorites[$topicId])) {
            return null;
        }

        return $this->favorites[$topicId];
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
