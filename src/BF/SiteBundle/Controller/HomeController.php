<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        //if the user is connected we redirect him to the logged page
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') OR $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirect($this->generateUrl('bf_site_logged_home'));
        }
        elseif( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')){
            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
            $listChallenges = $repository->findBy(array(),array('date' => 'desc'),5,0);

            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
            $latestChallenge = $repository->findBy(array(),array('date' => 'desc'),1,0);

            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
            $listVideos = $repository->findBy(array(),array('date' => 'desc'),5,0);

            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $listUsers = $repository->findBy(array(),array('points' => 'desc'),10,0);


            return $this->render('BFSiteBundle:Home:index.html.twig', array(
              'listChallenges' => $listChallenges,
              'listVideos' => $listVideos,
              'listUsers' => $listUsers,
              'latestChallenge' => $latestChallenge
            ));
        }
        
    }
    public function challengesAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findBy(array(),array('date' => 'desc'));

		return $this->render('BFSiteBundle:Home:challenges.html.twig', array(
	      'listChallenges' => $listChallenges,
	    ));
    }
    public function partnerChallengesAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findBy(array('partner' => '1'),array('date' => 'desc'));

        return $this->render('BFSiteBundle:Home:challenges.html.twig', array(
          'listChallenges' => $listChallenges,
        ));
    }
    public function challengeViewAction($id)
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
    	$challenge = $repository->find($id);
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $listVideos = $repository->findBy(
            array('challenge' => $challenge),
            array('date' => 'desc'),
            5,
            0);
        $rankUsers = $repository->findBy(
            array('challenge' => $challenge),
            array('repetitions' => 'desc'),
            5,
            0);

		return $this->render('BFSiteBundle:Home:challengeView.html.twig', array(
	      'listVideos' => $listVideos,
	      'challenge'  => $challenge,
          'rankUsers' => $rankUsers,
	    ));
    }
    public function rankingAction()
    {

        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $country = $user->getCountry();

            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $listUsersGlobal = $repository->findBy(array(),array('points' => 'desc'),10,0);
            $listUsersCountry = $repository->findBy(array('country' => $country),array('points' => 'desc'),10,0);


            return $this->render('BFSiteBundle:Home:ranking.html.twig',array(
                'listUsersGlobal' => $listUsersGlobal,
                'listUsersCountry' => $listUsersCountry,
                'country' => $country
                ));
        }
        else{
            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $listUsersGlobal = $repository->findBy(array(),array('points' => 'desc'),10,0);
           


            return $this->render('BFSiteBundle:Home:ranking.html.twig',array(
                'listUsersGlobal' => $listUsersGlobal,
                ));





        }    
    }
    public function aboutAction()
    {
		return $this->render('BFSiteBundle:Home:about.html.twig');
    }
    public function rulesAction()
    {
    	return $this->render('BFSiteBundle:Home:rules.html.twig');
    }
    public function contactAction()
    {
		return $this->render('BFSiteBundle:Home:contact.html.twig');
    }
    public function connectAction()
    {
        return $this->render('BFSiteBundle:Home:connect.html.twig');
    }
    public function loggedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $listNotifications = $user->getNotifications();

        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $listVideos = $repository->listHomeVideos();

        return $this->render('BFSiteBundle:Home:logged.html.twig', array(
          'listVideos' => $listVideos,
          'listNotifications' => $listNotifications,
        ));
    }
}
