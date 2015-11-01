<?php

namespace ApproveCode\Bundle\ApiBundle;

use ApproveCode\Bundle\ApiBundle\DependencyInjection\Compiler\GithubParametersMapperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApproveCodeApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GithubParametersMapperPass());
    }
}
