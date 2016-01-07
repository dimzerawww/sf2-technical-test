<?php

namespace StadLineUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('StadLineUserBundle:Default:index.html.twig');
    }
}
