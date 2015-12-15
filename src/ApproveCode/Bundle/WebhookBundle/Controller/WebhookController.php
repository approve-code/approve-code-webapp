<?php

namespace ApproveCode\Bundle\WebhookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function githubAction(Request $request)
    {
        // TODO: Extract signature check
        // Check security X-Hub-Signature
        $dirtySignature = $request->headers->get('X-Hub-Signature');

        if (null === $dirtySignature) {
            throw new \RuntimeException('X-Hub-Signature is missed');
        }

        $securityHelper = $this->get('ac.webhook.helper.security');
        $payload = $request->getContent();

        $cleanSignature = explode('=', $dirtySignature, 2);

        if (count($cleanSignature) !== 2) {
            throw new \RuntimeException('Incorrect signature');
        }

        if (false === $securityHelper->checkSha1Signature($payload, $cleanSignature[1])) {
            throw new \RuntimeException('Incorrect signature');
        }

        if (false === $request->headers->has('X-Github-Event')) {
            throw $this->createNotFoundException();
        }

        $githubEventHandler = $this->get('ac.webhook.handler.github_event');

        return $githubEventHandler->handle($request);
    }
}
