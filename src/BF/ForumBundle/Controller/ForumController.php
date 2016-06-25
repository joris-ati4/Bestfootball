<?php

namespace BF\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ForumController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BFForumBundle:Default:index.html.twig', array('name' => $name));
    }
    
}