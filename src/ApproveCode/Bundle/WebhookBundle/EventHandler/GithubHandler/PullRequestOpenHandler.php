<?php

namespace ApproveCode\Bundle\WebhookBundle\EventHandler\GithubHandler;

use Symfony\Bridge\Doctrine\RegistryInterface;

use ApproveCode\Bundle\ApiBundle\Factory\GithubClientFactory;
use ApproveCode\Bundle\UserBundle\Entity\Repository\RepositoryRepository;
use ApproveCode\Bundle\UserBundle\Exception\RepositoryNotFoundException;
use ApproveCode\Bundle\WebhookBundle\EventHandler\GithubEventHandlerInterface;

class PullRequestOpenHandler implements GithubEventHandlerInterface
{
    /**
     * @var GithubClientFactory
     */
    private $clientFactory;

    /**
     * @var string
     */
    private $statusContext;

    /**
     * @param RegistryInterface $doctrine
     * @param GithubClientFactory $clientFactory
     * @param string $statusContext
     */
    public function __construct(RegistryInterface $doctrine, GithubClientFactory $clientFactory, $statusContext)
    {
        $this->doctrine = $doctrine;
        $this->clientFactory = $clientFactory;
        $this->statusContext = $statusContext;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payload)
    {
        $fullName = $payload->pull_request->base->repo->full_name;
        $commitSha = $payload->pull_request->head->sha;

        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository || !$repository->getEnabled()) {
            throw new RepositoryNotFoundException();
        }

        // TODO: Think about this
        $client = $this->clientFactory->createClient($repository->getOwner()->getAccessToken());
        $statusesApi = $client->repository()->statuses();

        // TODO: Move it to statuses manager
        $statusesApi->create(
            $repository->getOwner()->getUsername(),
            $repository->getName(),
            $commitSha,
            [
                'state'       => 'pending',
                'description' => 'This PR pending code review',
                'context'     => $this->statusContext,
            ]
        );
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
        if (!in_array($payload->action, ['opened', 'reopened', 'synchronize'])) {
            return false;
        }

        $fullName = $payload->pull_request->base->repo->full_name;
        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository || !$repository->getEnabled()) {
            return false;
        }

        return true;
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
