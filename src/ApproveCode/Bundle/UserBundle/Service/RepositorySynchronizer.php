<?php

namespace ApproveCode\Bundle\UserBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;

use ApproveCode\Bundle\GithubBundle\Manager\GithubManager;
use ApproveCode\Bundle\UserBundle\Entity\Repository;
use ApproveCode\Bundle\UserBundle\Entity\User;

class RepositorySynchronizer
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
     * @param GithubManager $githubManager
     * @param RegistryInterface $doctrine
     */
    public function __construct(GithubManager $githubManager, RegistryInterface $doctrine)
    {
        $this->githubManager = $githubManager;
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $user
     */
    public function synchronizeUserRepositories(User $user)
    {
        $repositories = $this->githubManager->getUserRepositories($user->getUsername());
        $this->updateUserRepositories($user, $repositories);
    }

    /**
     * @param User $user
     * @param array $actualRepositories
     */
    protected function updateUserRepositories(User $user, array $actualRepositories)
    {
        // Reindex array by GH repository ids
        $actualRepositories = array_column($actualRepositories, null, 'id');

        $userRepositoryIds = [];
        /** @var Repository $repository */
        foreach ($user->getRepositories() as $repository) {
            // Repository was removed from github
            if (!array_key_exists($repository->getGithubId(), $actualRepositories)) {
                $user->getRepositories()->removeElement($repository);
                continue;
            }

            // Collect ids of user repositories
            $userRepositoryIds[] = $repository->getGithubId();
        }

        $repositoryManager = $this->doctrine->getManagerForClass(Repository::class);

        // Create repository entities for new repositories from github
        array_map(
            function ($repository) use ($userRepositoryIds, $user, $repositoryManager) {
                if (!in_array($repository['id'], $userRepositoryIds)) {
                    $repository = (new Repository())
                        ->setGithubId($repository['id'])
                        ->setFullName($repository['full_name'])
                        ->setName($repository['name'])
                        ->setOwner($user);

                    $repositoryManager->persist($repository);
                    $user->addRepository($repository);
                }

            },
            $actualRepositories
        );

        $repositoryManager->flush();
    }
}
