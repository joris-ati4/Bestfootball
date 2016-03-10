<?php

namespace BF\ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BFImageBundle:Default:index.html.twig', array('name' => $name));
    }
}
