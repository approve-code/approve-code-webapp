<?php

namespace ApproveCode\Bundle\RepositoryBundle\Controller;

use ApproveCode\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use ApproveCode\Bundle\RepositoryBundle\Entity\Repository;

class RepositoryController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $user = $this->getUser();

        $repositories = $this->get('doctrine')
            ->getManagerForClass('ApproveCodeRepositoryBundle:Repository')
            ->getRepository('ApproveCodeRepositoryBundle:Repository')
            ->getUserRepositories($user);

        if (!$repositories) {
            $this->get('ac.repository.service.repository_synchronizer')->synchronizeUserRepositories($user);
        }

        return $this->render(
            'ApproveCodeRepositoryBundle::list.html.twig',
            [
                'repositories' => $repositories,
                'user'         => $user,
            ]
        );
    }

    /**
     * @return RedirectResponse
     */
    public function syncAction()
    {
        $this->get('ac.repository.service.repository_synchronizer')->synchronizeUserRepositories($this->getUser());

        return $this->redirectToRoute('ac_repository_repository_list');
    }

    /**
     * @param Repository $repository
     * @return JsonResponse
     */
    public function toggleAction(Repository $repository)
    {
        if ($repository->getOwner()->getId() !== $this->getUser()->getId()) {
            $this->createAccessDeniedException();
        }

        $webhookManager = $this->get('ac.api.manager.webhook_manager');
        $webhookManager->toggleRepositoryWebhook($repository);

        return new JsonResponse(['success' => true]);
    }
}
