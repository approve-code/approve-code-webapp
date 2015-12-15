<?php

namespace ApproveCode\Bundle\WebhookBundle\Handler;

use Symfony\Component\HttpFoundation\Response;

interface GithubEventHandlerInterface
{
    /**
     * Return array of handleable events
     *
     * @return string[]
     */
    public function getHandleableEvents();

    /**
     * Can this handler handle current request
     *
     * @param object $payload
     * @return bool
     */
    public function isApplicable($payload);

    /**
     * @param object $payload
     * @return mixed|Response
     */
    public function handle($payload);
}
