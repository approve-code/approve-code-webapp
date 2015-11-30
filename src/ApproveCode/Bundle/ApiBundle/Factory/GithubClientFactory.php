<?php

namespace ApproveCode\Bundle\ApiBundle\Factory;

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

        if (!$this->token instanceof OAuthToken) {
            throw new \RuntimeException(sprintf('Unknown instance of token: %s', get_class($this->token)));
        }
    }

    /**
     * @return Client
     */
    public function createClient()
    {
        $client = new \Github\Client();
        $client->authenticate($this->token->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);

        return $client;
    }
}
