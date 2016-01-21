<?php

namespace ApproveCode\Bundle\GithubBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use ApproveCode\Bundle\GithubBundle\DependencyInjection\Compiler\GithubEventManagerPass;

class ApproveCodeGithubBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GithubEventManagerPass());
    }
}
