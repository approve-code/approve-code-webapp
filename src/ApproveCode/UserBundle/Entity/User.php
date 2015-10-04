<?php

namespace ApproveCode\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Entity\User as BaseUser;

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
     * @ORM\Column(name="github_id", type="string", nullable=true)
     */
    protected $githubID;

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
}
