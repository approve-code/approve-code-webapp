<?php

namespace ApproveCode\Bundle\WebhookBundle\Handler\Github;

use Symfony\Bridge\Doctrine\RegistryInterface;

use ApproveCode\Bundle\ApiBundle\Factory\GithubClientFactory;
use ApproveCode\Bundle\RepositoryBundle\Entity\Repository\RepositoryRepository;
use ApproveCode\Bundle\RepositoryBundle\Exception\RepositoryNotFoundException;
use ApproveCode\Bundle\WebhookBundle\Handler\GithubEventHandlerInterface;

class PullRequestHandler implements GithubEventHandlerInterface
{
    /**
     * @var GithubClientFactory
     */
    private $clientFactory;

    /**
     * @param RegistryInterface $doctrine
     * @param GithubClientFactory $clientFactory
     */
    public function __construct(RegistryInterface $doctrine, GithubClientFactory $clientFactory)
    {
        $this->doctrine = $doctrine;
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payload)
    {
        $fullName = $payload->pull_request->base->repo->full_name;
        $commitSha = $payload->pull_request->head->sha;


        $repository = $this->getRepositoryRepository()->findByFullName($fullName);

        if (null === $repository) {
            throw new RepositoryNotFoundException();
        }

        // TODO: This about this
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
                'context'     => 'code-review/approve-code',
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
        return in_array($payload->action, ['opened', 'reopened']);
    }

    /**
     * @return RepositoryRepository
     */
    public function getRepositoryRepository()
    {
        return $this->doctrine
            ->getManagerForClass('ApproveCodeRepositoryBundle:Repository')
            ->getRepository('ApproveCodeRepositoryBundle:Repository');
    }
}
