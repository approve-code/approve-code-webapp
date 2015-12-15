<?php

namespace ApproveCode\Bundle\WebhookBundle\Handler\Github;

use ApproveCode\Bundle\WebhookBundle\Handler\GithubEventHandlerInterface;

class PullRequestReviewCommentHandler implements GithubEventHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($payload)
    {
        // TODO
    }

    /**
     * {@inheritdoc}
     */
    public function getHandleableEvents()
    {
        return ['pull_request_review_comment'];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($payload)
    {
        return true;
    }
}
