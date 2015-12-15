<?php

namespace ApproveCode\Bundle\RepositoryBundle\Entity\Repository;

use ApproveCode\Bundle\RepositoryBundle\Entity\Repository;
use ApproveCode\Bundle\UserBundle\Entity\User;

use Doctrine\ORM\EntityRepository;

class RepositoryRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return Repository[]
     */
    public function getUserRepositories(User $user)
    {
        return $this->findBy(['owner' => $user]);
    }

    /**
     * @param string $fullName
     * @return null|Repository
     */
    public function findByFullName($fullName)
    {
        return $this->findOneBy(['fullName' => $fullName]);
    }
}
