<?php

namespace ApproveCode\Bundle\GithubBundle\EventHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GithubEventManager
{
    /**
     * List of handlers
     *
     * @var GithubEventHandlerInterface[]
     */
    protected $eventHandlers = [];

    /**
     * @param Request $request
     * @return Response
     * @throws NotFoundHttpException|\RuntimeException
     */
    public function handle(Request $request)
    {
        $event = $request->headers->get('X-Github-Event');

        if (!array_key_exists($event, $this->eventHandlers)) {
            throw new NotFoundHttpException('Can\'t handle this event');
        }

        $json = json_decode($request->getContent());
        if (null === $json) {
            throw new \RuntimeException('Invalid payload');
        }

        // Default response text
        $response = 'Inapplicable event';

        /** @var GithubEventHandlerInterface $handler */
        foreach ($this->eventHandlers[$event] as $handler) {
            if (!$handler->isApplicable($json)) {
                continue;
            }

            $response = $handler->handle($json);
            break;
        }

        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    }

    /**
     * @param GithubEventHandlerInterface $eventHandler
     */
    public function addEventHandler(GithubEventHandlerInterface $eventHandler)
    {
        // TODO: Think about priority
        foreach ($eventHandler->getHandleableEvents() as $handleableEvent) {
            $this->eventHandlers[$handleableEvent][] = $eventHandler;
        }
    }
}
