<?php

namespace ApproveCode\Bundle\WebhookBundle\Handler\Github;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Github\Exception\RuntimeException;

use ApproveCode\Bundle\ApiBundle\Factory\GithubClientFactory;
use ApproveCode\Bundle\RepositoryBundle\Entity\Repository\RepositoryRepository;
use ApproveCode\Bundle\RepositoryBundle\Exception\RepositoryNotFoundException;
use ApproveCode\Bundle\WebhookBundle\Handler\GithubEventHandlerInterface;

class PullRequestReviewCommentHandler implements GithubEventHandlerInterface
{
    /**
     * @var GithubClientFactory
     */
    protected $clientFactory;

    /**
     * @param RegistryInterface $doctrine
     * @param GithubClientFactory $clientFactory
     * @param string $statusContext
     */
    public function __construct(
        RegistryInterface $doctrine,
        GithubClientFactory $clientFactory,
        $statusContext
    ) {
        $this->doctrine = $doctrine;
        $this->clientFactory = $clientFactory;
        $this->statusContext = $statusContext;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payload)
    {
        $fullName = $payload->repository->full_name;

        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository) {
            throw new RepositoryNotFoundException();
        }

        // TODO: Think about this
        $client = $this->clientFactory->createClient($repository->getOwner()->getAccessToken());
        $pullRequestsApi = $client->pullRequest();

        list($ownerName, $repoName) = explode('/', $fullName, 2);
        $commits = $pullRequestsApi->commits($ownerName, $repoName, $payload->issue->number);
        $lastCommit = end($commits);

        // TODO: Move it to statuses manager
        $statusesApi = $client->repository()->statuses();
        $statusesApi->create(
            $repository->getOwner()->getUsername(),
            $repository->getName(),
            $lastCommit['sha'],
            [
                'state'       => 'success',
                'description' => 'This PR reviewed',
                'context'     => $this->statusContext,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHandleableEvents()
    {
        return ['issue_comment'];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($payload)
    {
        // Handle only created action
        if ('created' !== $payload->action) {
            return false;
        }

        // Handle only pull requests
        if (!isset($payload->issue->pull_request)) {
            return false;
        }

        // Handle only comments with status markers
        if (null === $this->getStatusMarker($payload->comment->body)) {
            return false;
        }

        $fullName = $payload->repository->full_name;
        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository || !$repository->getEnabled()) {
            throw new RepositoryNotFoundException();
        }

        // TODO: Think about this
        $client = $this->clientFactory->createClient($repository->getOwner()->getAccessToken());
        $collaboratorsApi = $client->repository()->collaborators();

        list($ownerName, $repoName) = explode('/', $payload->repository->full_name, 2);
        try {
            $collaboratorsApi->check($ownerName, $repoName, $payload->comment->user->login);
        } catch (RuntimeException $e) {
            // User isn't a collaborator
            if (404 === $e->getCode()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Try to find status marker
     *
     * @param string $comment
     * @return null|string
     */
    protected function getStatusMarker($comment)
    {
        $statusMarker = null;

        // TODO: Add additional approve markers
        $applicableCommentMarkers = [':+1:', ':-1:'];
        foreach ($applicableCommentMarkers as $applicableMarker) {
            if (false !== strpos($comment, $applicableMarker)) {
                $statusMarker = $applicableMarker;
                break;
            }
        }

        return $statusMarker;
    }

    /**
     * @return RepositoryRepository
     */
    protected function getRepositoryRepository()
    {
        return $this->doctrine
            ->getManagerForClass('ApproveCodeRepositoryBundle:Repository')
            ->getRepository('ApproveCodeRepositoryBundle:Repository');
    }
}