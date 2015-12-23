<?php

namespace ApproveCode\Bundle\WebhookBundle\EventHandler\GithubHandler;

use ApproveCode\Bundle\WebhookBundle\EventHandler\GithubEventHandlerInterface;

class PingHandler implements GithubEventHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function getHandleableEvents()
    {
        return ['ping'];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($payload)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($payload)
    {
        return 'pong';
    }
}
