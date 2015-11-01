<?php

namespace ApproveCode\Bundle\RepositoryBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Github\Client;

use ApproveCode\Bundle\RepositoryBundle\Entity\Repository;
use ApproveCode\Bundle\ApiBundle\Wrapper\GithubApiWrapper;
use ApproveCode\Bundle\UserBundle\Entity\User;

class RepositorySynchronizer
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @param GithubApiWrapper $githubWrapper
     * @param RegistryInterface $doctrine
     */
    public function __construct(GithubApiWrapper $githubWrapper, RegistryInterface $doctrine)
    {
        $this->client = $githubWrapper->getClient();
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $user
     */
    public function synchronizeUserRepositories(User $user)
    {
        $repositories = $this->client->user()->repositories($user->getUsername());
        $this->updateUserRepositories($user, $repositories);
    }

    /**
     * @param User $user
     * @param array $repositories
     */
    protected function updateUserRepositories(User $user, array $repositories)
    {
        // Reindex array by GH repository ids
        $repositories = array_column($repositories, null, 'id');

        // Remove obsolete repositories
        $user->getRepositories()->filter(
            function (Repository $repository) use ($repositories) {
                return array_key_exists($repository->getGithubId(), $repositories);
            }
        );

        // Get current user repository GH ids
        $repositoryGHIds = $user->getRepositories()->map(
            function (Repository $repository) {
                return $repository->getGithubId();
            }
        )->toArray();

        $repositoryManager = $this->doctrine->getManagerForClass(Repository::class);

        //
        array_map(
            function ($repository) use ($repositoryGHIds, $user, $repositoryManager) {
                if (!in_array($repository['id'], $repositoryGHIds)) {
                    $repository = (new Repository())
                        ->setGithubId($repository['id'])
                        ->setFullName($repository['full_name'])
                        ->setName($repository['name'])
                        ->setOwner($user);

                    $repositoryManager->persist($repository);
                    $user->addRepository($repository);
                }

            },
            $repositories
        );

        $repositoryManager->flush();
    }
}
