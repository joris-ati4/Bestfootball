<?php

namespace BF\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BFUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
