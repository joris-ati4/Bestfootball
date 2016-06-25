<?php

namespace BF\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PostController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BFForumBundle:Default:index.html.twig', array('name' => $name));
    }
    public function addAction($name)
    {
        return $this->render('BFForumBundle:Default:index.html.twig', array('name' => $name));
    }
    public function delAction($name)
    {
        return $this->render('BFForumBundle:Default:index.html.twig', array('name' => $name));
    }
}