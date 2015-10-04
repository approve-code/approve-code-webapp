<?php

namespace ApproveCode\WebBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('ApproveCodeWebBundle::index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
