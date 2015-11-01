<?php

namespace ApproveCode\Bundle\UserBundle;

use ApproveCode\Bundle\ApiBundle\DependencyInjection\Compiler\GithubParametersMapperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApproveCodeUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GithubParametersMapperPass());
    }
}
