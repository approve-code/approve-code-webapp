<?php

namespace ApproveCode\Bundle\ApiBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Github\Api\User;
use Github\ResultPager;
use Github\Api\Repository\Hooks;
use Github\Exception\RuntimeException;

class GithubManager
{
    /**
     * @var ResultPager
     */
    private $pager;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var User
     */
    private $userApi;

    /**
     * @var Hooks
     */
    private $hooksApi;

    /**
     * @param User $userApi
     * @param Hooks $hooksApi
     * @param ResultPager $pager
     * @param string $webhookUrl
     */
    public function __construct(User $userApi, Hooks $hooksApi, ResultPager $pager, $webhookUrl)
    {
        $this->userApi = $userApi;
        $this->hooksApi = $hooksApi;
        $this->pager = $pager;
        $this->webhookUrl = $webhookUrl;
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
            $result = $this->hooksApi->create($username, $repository, $params);
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
            $this->hooksApi->remove($username, $repository, $webhookId);
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
        return $this->pager->fetchAll($this->userApi, 'repositories', [$username]);
    }
}
