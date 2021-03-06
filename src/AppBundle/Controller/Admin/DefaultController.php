<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_homepage")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(':admin/default:index.html.twig');
    }
}
