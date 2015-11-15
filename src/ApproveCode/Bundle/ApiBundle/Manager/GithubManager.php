<?php

namespace ApproveCode\Bundle\ApiBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Erivello\GithubApiBundle\Service\GithubService;

use Github\Client;
use Github\Exception\RuntimeException;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class GithubManager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $webhookUrl;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param GithubService $github
     * @param string $webhookUrl
     */
    public function __construct(TokenStorageInterface $tokenStorage, GithubService $github, $webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
        $token = $tokenStorage->getToken();

        if (!$token instanceof OAuthToken) {
            throw new \RuntimeException(sprintf('Unknown instance of token: %s', get_class($token)));
        }

        $this->client = $github->getClient();
        $this->client->authenticate($token->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);
    }

    /**
     * Create github webhook
     *
     * @param string $username
     * @param string $repository
     * @return int|null
     * @throws \Github\Exception\MissingArgumentException
     */
    public function createWebhook($username, $repository)
    {
        $params = [
            'name'   => 'web',
            'active' => true,
            'events' => [
                'pull_request',
                'pull_request_review_comment',
            ],
            'config' => [
                'url'          => $this->webhookUrl,
                'content_type' => 'json',
                'insecure_ssl' => parse_url($this->webhookUrl, PHP_URL_SCHEME) === 'https' ? 0 : 1,
            ],
        ];

        try {
            $result = $this->client->repositories()->hooks()->create($username, $repository, $params);
        } catch (RuntimeException $e) {
            // Possible not found exception
            return null;
        }

        return isset($result['id']) ? $result['id'] : null;
    }

    /**
     * Remove github webhook
     *
     * @param string $username
     * @param string $repository
     * @param int $webhookId
     * @return bool
     */
    public function removeWebhook($username, $repository, $webhookId)
    {
        try {
            $this->client->repositories()->hooks()->remove($username, $repository, $webhookId);
        } catch (RuntimeException $e) {
            // Possible not found exception
            return false;
        }

        return true;
    }

    /**
     * Get repositories data
     *
     * @param string $username
     * @return array
     */
    public function getUserRepositories($username)
    {
        return $this->client->user()->repositories($username);
    }
}
