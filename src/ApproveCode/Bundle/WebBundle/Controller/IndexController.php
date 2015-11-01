<?php

namespace ApproveCode\Bundle\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
