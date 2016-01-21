<?php

namespace ApproveCode\Bundle\GithubBundle\EventHandler\GithubHandler;

use Symfony\Bridge\Doctrine\RegistryInterface;

use ApproveCode\Bundle\GithubBundle\EventHandler\GithubEventHandlerInterface;
use ApproveCode\Bundle\GithubBundle\Helper\GithubApiHelper;
use ApproveCode\Bundle\UserBundle\Exception\RepositoryNotFoundException;
use ApproveCode\Bundle\UserBundle\Entity\Repository\RepositoryRepository;

class PullRequestOpenHandler implements GithubEventHandlerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var GithubApiHelper
     */
    protected $githubApiHelper;

    /**
     * @param RegistryInterface $doctrine
     * @param GithubApiHelper $githubApiHelper
     */
    public function __construct(RegistryInterface $doctrine, GithubApiHelper $githubApiHelper)
    {
        $this->doctrine = $doctrine;
        $this->githubApiHelper = $githubApiHelper;
    }

    /**
     * {@inheritdoc}
     * @throws RepositoryNotFoundException
     */
    public function handle($payload)
    {
        $fullName = $payload->pull_request->base->repo->full_name;
        $commitSha = $payload->pull_request->head->sha;

        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository || !$repository->getEnabled()) {
            throw new RepositoryNotFoundException();
        }

        $this->githubApiHelper->createStatus($repository, $commitSha, [
            'state' => 'pending',
            'description' => 'This PR pending code review'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandleableEvents()
    {
        return ['pull_request'];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($payload)
    {
        if (!in_array($payload->action, ['opened', 'reopened', 'synchronize'], true)) {
            return false;
        }

        $fullName = $payload->pull_request->base->repo->full_name;
        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        return !(null === $repository || !$repository->getEnabled());
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
