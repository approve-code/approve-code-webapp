<?php

namespace ApproveCode\Bundle\GithubBundle\Factory;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Github\Client;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class GithubClientFactory
{
    /**
     * @var OAuthToken
     */
    private $token;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @param string|null $accessToken
     * @return Client
     */
    public function createClient($accessToken = null)
    {
        $client = new Client();

        if ($this->token instanceof OAuthToken && null === $accessToken) {
            $client->authenticate($this->token->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);
        } elseif (null !== $accessToken) {
            $client->authenticate($accessToken, null, Client::AUTH_HTTP_TOKEN);
        }

        return $client;
    }
}
