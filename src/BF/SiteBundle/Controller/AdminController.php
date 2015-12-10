<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Challenge;
use BF\UserBundle\Entity\User;
//les types
use BF\SiteBundle\Form\VideoType;
use BF\SiteBundle\Form\ChallengeType;

class AdminController extends Controller
{
    public function addChallengeAction(request $request)
    {
	    // On crée un objet Challenge
	    $challenge = new Challenge();

	    $form = $this->get('form.factory')->create(new ChallengeType, $challenge);
	    
	    if ($form->handleRequest($request)->isValid()) {
	      $em = $this->getDoctrine()->getManager();
	      $em->persist($challenge);
	      $em->flush();

	      $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

	      return $this->redirect($this->generateUrl('bf_site_homepage'));
	    }

	    return $this->render('BFSiteBundle:Admin:addChallenge.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }
    public function indexAction()
    {
	    return $this->render('BFSiteBundle:Admin:index.html.twig');
    }
    public function usersAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$listUsers = $repository->findall();
	    return $this->render('BFSiteBundle:Admin:users.html.twig',array(
	    	'listUsers' => $listUsers,
	    	));
    }
    public function challengesAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
    	$listChallenges = $repository->findall();
	    return $this->render('BFSiteBundle:Admin:challenges.html.twig',array(
	    	'listChallenges' => $listChallenges,
	    	));
    }

}