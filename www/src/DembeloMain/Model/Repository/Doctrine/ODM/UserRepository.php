<?php

namespace DembeloMain\Model\Repository\Doctrine\ODM;

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class UserRepository
 * @package DembeloMain\Model\Repository\Doctrine\ODM
 */
class UserRepository extends DocumentRepository implements UserRepositoryInterface
{

    /**
     * Save a user
     * @param User $user
     * @return User
     */
    public function save(User $user)
    {
        $this->dm->persist($user);
        $this->dm->flush();

        return $user;
    }

    /**
     * Find a user by email
     * @param string $email
     * @return User
     */
    public function findByEmail($email)
    {
        /** @var User $user */
        $user = $this->findOneBy(array('email' => $email));

        return $user;
    }
}
