<?php

namespace ApproveCode\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * TODO Need to remove
 */
class GithubParametersMapperPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $githubClientId = $container->getParameter('github_client_id');
        $githubClientSecret = $container->getParameter('github_client_secret');

        if (!$githubClientId || !$githubClientSecret) {
            throw new \RuntimeException(
                'You should configure github_client_id and github_client_secret in your parameters.yml'
            );
        }
    }
}
