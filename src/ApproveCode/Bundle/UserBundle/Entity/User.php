<?php

namespace ApproveCode\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Entity\User as BaseUser;

use ApproveCode\Bundle\UserBundle\Entity\Repository;

/**
 * @ORM\Entity
 * @ORM\Table(name="ac_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="github_id", type="string", length=255, nullable=true)
     */
    protected $githubID;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255)
     */
    protected $accessToken;

    /**
     * @ORM\OneToMany(
     *  targetEntity="ApproveCode\Bundle\UserBundle\Entity\Repository",
     *  mappedBy="owner",
     *  orphanRemoval=true
     * )
     *
     * @var ArrayCollection|Repository[]
     */
    protected $repositories;

    /**
     * Get githubId
     *
     * @return string
     */
    public function getGithubID()
    {
        return $this->githubID;
    }

    /**
     * Set githubId
     *
     * @param string $githubID
     *
     * @return $this
     */
    public function setGithubID($githubID)
    {
        $this->githubID = $githubID;

        return $this;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     * @return User
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Add repositories
     *
     * @param Repository $repository
     * @return User
     */
    public function addRepository(Repository $repository)
    {
        if (!$this->repositories->contains($repository)) {
            $this->repositories[] = $repository;
        }

        return $this;
    }

    /**
     * Remove repositories
     *
     * @param Repository $repository
     */
    public function removeRepository(Repository $repository)
    {
        $this->repositories->removeElement($repository);
    }

    /**
     * Get repositories
     *
     * @return Collection
     */
    public function getRepositories()
    {
        return $this->repositories;
    }
}
