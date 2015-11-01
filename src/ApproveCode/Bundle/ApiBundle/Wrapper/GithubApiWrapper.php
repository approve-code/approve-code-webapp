<?php

namespace ApproveCode\Bundle\ApiBundle\Wrapper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

use Erivello\GithubApiBundle\Service\GithubService;

use Github\Client;

class GithubApiWrapper
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

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
