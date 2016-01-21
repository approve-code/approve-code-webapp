<?php

namespace ApproveCode\Bundle\GithubBundle\EventHandler\GithubHandler;

use Symfony\Bridge\Doctrine\RegistryInterface;

use ApproveCode\Bundle\WebhookBundle\Helper\StatusMarkerHelper;
use ApproveCode\Bundle\GithubBundle\Helper\GithubApiHelper;
use ApproveCode\Bundle\GithubBundle\EventHandler\GithubEventHandlerInterface;
use ApproveCode\Bundle\UserBundle\Entity\Repository\RepositoryRepository;
use ApproveCode\Bundle\UserBundle\Exception\RepositoryNotFoundException;

class PullRequestReviewCommentHandler implements GithubEventHandlerInterface
{
    /**
     * @var GithubApiHelper
     */
    protected $githubApiHelper;

    /**
     * @var StatusMarkerHelper
     */
    protected $statusMarkerHelper;

    /**
     * @param RegistryInterface $doctrine
     * @param StatusMarkerHelper $statusMarkerHelper
     * @param GithubApiHelper $githubApiHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        GithubApiHelper $githubApiHelper,
        StatusMarkerHelper $statusMarkerHelper
    ) {
        $this->doctrine = $doctrine;
        $this->githubApiHelper = $githubApiHelper;
        $this->statusMarkerHelper = $statusMarkerHelper;
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

        list($ownerName, $repoName) = explode('/', $fullName, 2);
        $commit = $this->githubApiHelper->getLastCommitInPR(
            $repository->getOwner()->getAccessToken(),
            $ownerName,
            $repoName,
            $payload->issue->number
        );

        $context = [];
        $statusMarker = $this->statusMarkerHelper->getStatusMarker($payload->comment->body);
        switch ($this->statusMarkerHelper->getMarkerType($statusMarker)) {
            case StatusMarkerHelper::APPROVE_TYPE:
                $context['state'] = 'success';
                $context['description'] = 'This PR was reviewed';
                break;
            case StatusMarkerHelper::UNDER_REVIEW_TYPE:
                $context['state'] = 'pending';
                $context['description'] = 'This PR pending code review';
                break;
            case StatusMarkerHelper::REJECT_TYPE:
                $context['state'] = 'failure';
                $context['description'] = 'This PR was rejected';
                break;
            default:
                return;
        }

        $this->githubApiHelper->createStatus($repository, $commit['sha'], $context);
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
     * @throws RepositoryNotFoundException
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

        list($ownerName, $repoName) = explode('/', $payload->repository->full_name, 2);

        return $this->githubApiHelper->checkCollaborator(
            $repository->getOwner()->getAccessToken(),
            $ownerName,
            $repoName,
            $payload->comment->user->login
        );
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
