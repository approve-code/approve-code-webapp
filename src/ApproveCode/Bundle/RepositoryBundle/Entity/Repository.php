<?php

namespace ApproveCode\Bundle\RepositoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ApproveCode\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="ApproveCode\Bundle\RepositoryBundle\Entity\Repository\RepositoryRepository")
 * @ORM\Table(name="ac_repository")
 */
class Repository
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
     * @ORM\Column(name="github_id", type="integer", unique=true)
     * @TODO: Should be marked as ID
     *
     * @var int
     */
    protected $githubId;

    /**
     * @ORM\Column(name="full_name", type="string", length=255, unique=true)
     *
     * @var string
     */
    protected $fullName;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="webhook_id", type="bigint", nullable=true)
     *
     * @var int
     */
    protected $webhookId;

    /**
     * @ORM\ManyToOne(targetEntity="ApproveCode\Bundle\UserBundle\Entity\User", inversedBy="repositories")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     *
     * @var User
     */
    protected $owner;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set githubId
     *
     * @param integer $githubId
     * @return Repository
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;

        return $this;
    }

    /**
     * Get githubId
     *
     * @return integer
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return Repository
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Repository
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return (bool)$this->webhookId;
    }

    /**
     * Set webhookId
     *
     * @param integer $webhookId
     *
     * @return Repository
     */
    public function setWebhookId($webhookId = null)
    {
        $this->webhookId = $webhookId;

        return $this;
    }

    /**
     * Get webhookId
     *
     * @return integer
     */
    public function getWebhookId()
    {
        return $this->webhookId;
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return Repository
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }
}
