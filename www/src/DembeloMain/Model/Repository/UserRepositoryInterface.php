<?php

namespace DembeloMain\Model\Repository;

use DembeloMain\Document\User;

/**
 * Interface UserRepositoryInterface
 * @package DembeloMain\Model\Repository
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by id
     * @param string $id
     * @return User
     */
    public function find($id);

    /**
     * Find a user by email
     * @param string $email
     * @return User
     */
    public function findByEmail($email);

    /**
     * Find all users
     * @return User[]
     */
    public function findAll();

    /**
     * Save a user
     * @param User $user
     * @return User
     */
    public function save(User $user);
}
