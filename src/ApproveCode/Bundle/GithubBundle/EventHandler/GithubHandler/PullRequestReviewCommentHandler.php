<?php

namespace ApproveCode\Bundle\GithubBundle\EventHandler\GithubHandler;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Github\Exception\RuntimeException;

use ApproveCode\Bundle\GithubBundle\Factory\GithubClientFactory;
use ApproveCode\Bundle\GithubBundle\Helper\StatusMarkerHelper;
use ApproveCode\Bundle\UserBundle\Entity\Repository\RepositoryRepository;
use ApproveCode\Bundle\UserBundle\Exception\RepositoryNotFoundException;
use ApproveCode\Bundle\GithubBundle\EventHandler\GithubEventHandlerInterface;

class PullRequestReviewCommentHandler implements GithubEventHandlerInterface
{
    /**
     * @var GithubClientFactory
     */
    protected $clientFactory;

    /**
     * @var StatusMarkerHelper
     */
    private $statusMarkerHelper;

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

        $statusMarker = $this->statusMarkerHelper->getStatusMarker($payload->comment->body);

        switch ($this->statusMarkerHelper->getMarkerType($statusMarker)) {
            case StatusMarkerHelper::APPROVE_TYPE:
                $state = 'success';
                $description = 'This PR was reviewed';
                break;
            case StatusMarkerHelper::UNDER_REVIEW_TYPE:
                $state = 'pending';
                $description = 'This PR pending code review';
                break;
            case StatusMarkerHelper::REJECT_TYPE:
                $state = 'failure';
                $description = 'This PR was rejected';
                break;
            default:
                return;
        }


        // TODO: Move it to statuses manager
        $statusesApi = $client->repository()->statuses();
        $statusesApi->create(
            $repository->getOwner()->getUsername(),
            $repository->getName(),
            $lastCommit['sha'],
            [
                'state'       => $state,
                'description' => $description,
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
        if (null === $this->statusMarkerHelper->getStatusMarker($payload->comment->body)) {
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
     * @param StatusMarkerHelper $statusMarkerHelper
     */
    public function setStatusMarkerHelper(StatusMarkerHelper $statusMarkerHelper)
    {
        $this->statusMarkerHelper = $statusMarkerHelper;
    }

    /**
     * @return RepositoryRepository
     */
    protected function getRepositoryRepository()
    {
        return $this->doctrine
            ->getManagerForClass('ApproveCodeUserBundle:Repository')
            ->getRepository('ApproveCodeUserBundle:Repository');
    }
}
