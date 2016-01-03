<?php

namespace ApproveCode\Bundle\GithubBundle\EventHandler\GithubHandler;

use ApproveCode\Bundle\GithubBundle\EventHandler\GithubEventHandlerInterface;

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
