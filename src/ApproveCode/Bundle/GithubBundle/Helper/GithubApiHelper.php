<?php

namespace ApproveCode\Bundle\GithubBundle\Helper;

use Github\Client;
use Github\Api\Repository\Statuses;
use Github\Exception\RuntimeException;

use ApproveCode\Bundle\UserBundle\Entity\Repository;
use ApproveCode\Bundle\GithubBundle\Factory\GithubClientFactory;

class GithubApiHelper
{
    /**
     * @var Client[]
     */
    protected $clients;

    /**
     * @var GithubClientFactory
     */
    protected $clientFactory;

    /**
     * @var string
     */
    protected $statusContext;

    /**
     * @param GithubClientFactory $clientFactory
     * @param string $statusContext
     */
    public function __construct(GithubClientFactory $clientFactory, $statusContext)
    {
        $this->clientFactory = $clientFactory;
        $this->statusContext = $statusContext;
    }

    /**
     * @param Repository $repository
     * @param string $commit
     * @param array $context
     * @throws \Github\Exception\MissingArgumentException
     */
    public function createStatus(Repository $repository, $commit, $context)
    {
        $statusesApi = $this->getStatusesApi($repository->getOwner()->getAccessToken());

        $repositoryOwnerName = $repository->getOwner()->getUsername();
        $repositoryName = $repository->getName();

        $statusesApi->create(
            $repositoryOwnerName,
            $repositoryName,
            $commit,
            [
                'state'       => isset($context['state']) ? $context['state'] : null,
                'description' => $context['description'],
                'context'     => $this->statusContext,
            ]
        );
    }

    /**
     * @param string $accessToken
     * @param string $ownerName
     * @param string $repoName
     * @param int $id
     *
     * @return array Last commit information
     */
    public function getLastCommitInPR($accessToken, $ownerName, $repoName, $id)
    {
        $pullRequestsApi = $this->getPullRequestApi($accessToken);
        $commits = $pullRequestsApi->commits($ownerName, $repoName, $id);

        return end($commits);
    }

    /**
     * @param string $accessToken
     * @param string $ownerName
     * @param string $repoName
     * @param string $username
     * @return bool true if user is collaborator, otherwise false
     */
    public function checkCollaborator($accessToken, $ownerName, $repoName, $username)
    {
        $collaboratorApi = $this->getCollaboratorApi($accessToken);

        try {
            $collaboratorApi->check($ownerName, $repoName, $username);
        } catch (RuntimeException $e) {
            // User isn't a collaborator
            if (404 !== $e->getCode()) {
                // TODO: log exception
            }
            return false;
        }

        return true;
    }

    protected function getCollaboratorApi($accessToken)
    {
        $client = $this->getClient($accessToken);
        return $client->repository()->collaborators();
    }

    /**
     * @param string $accessToken
     * @return \Github\Api\PullRequest
     */
    protected function getPullRequestApi($accessToken)
    {
        $client = $this->getClient($accessToken);

        return $client->pullRequest();
    }

    /**
     * @param string $accessToken
     * @return Statuses
     */
    protected function getStatusesApi($accessToken)
    {
        $client = $this->getClient($accessToken);
        return $client->repository()->statuses();
    }

    /**
     * @param string $accessToken
     * @return Client
     */
    protected function getClient($accessToken)
    {
        if (!array_key_exists($accessToken, $this->clients)) {
            $this->clients[$accessToken] = $this->clientFactory->createClient($accessToken);
        }
        return $this->clients[$accessToken];
    }
}
