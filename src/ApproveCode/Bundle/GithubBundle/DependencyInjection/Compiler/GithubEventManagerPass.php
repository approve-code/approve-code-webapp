<?php

namespace ApproveCode\Bundle\GithubBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GithubEventManagerPass implements CompilerPassInterface
{
    const GITHUB_EVENT_MANAGER_SERVICE = 'ac.github.event_handler.github_event_manager';
    const EVENT_HANDLER_TAG = 'github.event_handler';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::GITHUB_EVENT_MANAGER_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::EVENT_HANDLER_TAG);

        if (0 === count($taggedServices)) {
            return;
        }

        $loaderDefinition = $container->findDefinition(self::GITHUB_EVENT_MANAGER_SERVICE);

        foreach (array_keys($taggedServices) as $id) {
            $loaderDefinition->addMethodCall(
                'addEventHandler',
                [new Reference($id)]
            );
        }
    }
}
