<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InformationController extends Controller
{
    public function pressAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:press.html.twig', array(
              
            ));
    }
    public function partnersAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:partners.html.twig', array(
             
            ));
    }
    public function privacyAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:privacy.html.twig', array(
             
            ));
    }
    public function termsAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:terms.html.twig', array(
              
            ));
    }
    public function conditionsAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:conditions.html.twig', array(
              
            ));
    }
    public function communityAction(request $request)
    {
        return $this->render('BFSiteBundle:Home:community.html.twig', array(
              
            ));
    }
    public function explinationAction()
    {
        return $this->render('BFSiteBundle:Home:explications.html.twig', array(
              
            ));
    }
}
