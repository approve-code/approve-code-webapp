<?php

namespace ApproveCode\Bundle\ApiBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Erivello\GithubApiBundle\Service\GithubService;

use Github\Client;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class GithubWebhookManager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param GithubService $github
     */
    public function __construct(TokenStorageInterface $tokenStorage, GithubService $github)
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof OAuthToken) {
            throw new \RuntimeException(sprintf('Unknown instance of token: %s', get_class($token)));
        }

        $this->client = $github->getClient();
        $this->client->authenticate($token->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);
    }

    public function createWebhook($username, $repository)
    {
        $params = [];
        $result = $this->client->repositories()->hooks()->create($username, $repository, $params);
    }

    /**
     * Get repositories data
     *
     * @param $username
     * @return array
     */
    public function getUserRepositories($username)
    {
        return $this->client->user()->repositories($username);
    }
}
