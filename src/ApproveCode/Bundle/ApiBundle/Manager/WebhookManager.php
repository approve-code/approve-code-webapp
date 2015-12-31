<?php

namespace ApproveCode\Bundle\ApiBundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Doctrine\Common\Util\ClassUtils;

use ApproveCode\Bundle\UserBundle\Entity\User;
use ApproveCode\Bundle\UserBundle\Entity\Repository;

class WebhookManager
{
    /**
     * @var GithubManager
     */
    protected $githubManager;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param RegistryInterface $doctrine
     * @param TokenStorageInterface $tokenStorage
     * @param GithubManager $githubManager
     */
    public function __construct(
        RegistryInterface $doctrine,
        TokenStorageInterface $tokenStorage,
        GithubManager $githubManager
    ) {
        $this->doctrine = $doctrine;
        $this->githubManager = $githubManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Repository $repository
     */
    public function toggleRepositoryWebhook(Repository $repository)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if ($repository->getEnabled()) {
            $this->githubManager->removeWebhook(
                $user->getUsername(),
                $repository->getName(),
                $repository->getWebhookId()
            );
            $webhookId = null;
        } else {
            $webhookId = $this->githubManager->createWebhook($user->getUsername(), $repository->getName());
        }

        // Update state of repository webhook
        $repository->setWebhookId($webhookId);

        $repositoryManager = $this->doctrine->getManagerForClass(ClassUtils::getClass($repository));
        $repositoryManager->flush();
    }
}
