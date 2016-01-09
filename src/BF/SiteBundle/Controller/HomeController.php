<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

//Les entités
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Challenge;
use BF\UserBundle\Entity\User;
use BF\UserBundle\Entity\UserRepository;
use BF\SiteBundle\Entity\VideoRepository;
//les types
use BF\UserBundle\Form\UserSearchType;
use BF\SiteBundle\Form\VideoType;
use BF\SiteBundle\Form\VideoEditType;
use BF\SiteBundle\Form\ChallengeType;


class HomeController extends Controller
{
    public function indexAction(request $request)
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
    public function challengesAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findBy(array(),array('date' => 'desc'));

		return $this->render('BFSiteBundle:Home:challenges.html.twig', array(
	      'listChallenges' => $listChallenges,
          'search' => $search->createView(),
	    ));
    }
    public function partnerChallengesAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findBy(array('partner' => '1'),array('date' => 'desc'));

        return $this->render('BFSiteBundle:Home:challenges.html.twig', array(
          'listChallenges' => $listChallenges,
          'search' => $search->createView(),
            
        ));
    }
    public function challengeViewAction($id,request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

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
          'search' => $search->createView(),
	    ));
    }
    public function rankingAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $country = $user->getCountry();

            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $listUsersGlobal = $repository->findBy(array(),array('points' => 'desc'),10,0);
            $listUsersCountry = $repository->findBy(array('country' => $country),array('points' => 'desc'),10,0);


            return $this->render('BFSiteBundle:Home:ranking.html.twig',array(
                'listUsersGlobal' => $listUsersGlobal,
                'listUsersCountry' => $listUsersCountry,
                'country' => $country,
              'search' => $search->createView(),
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
    public function aboutAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

		return $this->render('BFSiteBundle:Home:about.html.twig', array(
              'search' => $search->createView(),
            ));
    }
    public function rulesAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

    	return $this->render('BFSiteBundle:Home:rules.html.twig', array(
              'search' => $search->createView(),
            ));
    }
    public function contactAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

		return $this->render('BFSiteBundle:Home:contact.html.twig', array(
              'search' => $search->createView(),
            ));
    }
    public function connectAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

        return $this->render('BFSiteBundle:Home:connect.html.twig', array(
              'search' => $search->createView(),
            ));
    }
    public function loggedAction(request $request)
    {
        //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }


        $user = $this->container->get('security.context')->getToken()->getUser();

        $listNotifications = $user->getNotifications();

        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $listVideos = $repository->listHomeVideos();

        $points = $user->getPoints();
          if( '0'<= $points && $points < '600'){$level = 'Incognito'; $percent=($points/600)*100;$min=0;$max=600;$style='progress-bar-success';} //incognito
          if( '600'<= $points && $points < '1200'){$level = 'Promising Star';$percent=(($points-600)/600)*100;$min=600;$max=1200;$style='progress-bar-success';}
          if( '1200'<= $points && $points < '1900'){$level = 'Rising Star';$percent=(($points-1200)/700)*100;$min=1200;$max=1900;$style='progress-bar-info';}
          if( '1900'<= $points && $points < '2500'){$level = 'Confirmed Star';$percent=(($points-1900)/600)*100;$min=1900;$max=2500;$style='progress-bar-warning';}
          if( '2500'<= $points){$level = 'Legend';$percent=(($points-2500)/2500)*100;$min=2500;$max=5000;$style='progress-bar-danger';}


        //now we are going to determine the place of the user.

        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $globalRank = $repository->globalRanking();
        $countryRank = $repository->countryRanking($user->getCountry());
        $stateRank = $repository->stateRanking($user->getState());

        $globalRank = array_search($user, $globalRank) + 1;
        $countryRank = array_search($user, $countryRank) + 1;
        $stateRank = array_search($user, $stateRank) + 1;
        $ranking = array($globalRank,$countryRank,$stateRank);
        
        return $this->render('BFSiteBundle:Home:logged.html.twig', array(
          'listVideos' => $listVideos,
          'listNotifications' => $listNotifications,
          'user' => $user,
          'level' => $level,
          'percent' => $percent,
          'min' => $min,
          'max' => $max,
          'style' => $style,
          'ranking'=> $ranking,
          'search' => $search->createView(),
        ));
    }
    public function searchAction(request $request)
    {
        $term = $request->get('query');
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $array = $repository->findUserLike($term);

        //making the array for the plugin
        $response = new Response(json_encode($array));
        $response -> headers -> set('Content-Type', 'application/json');
        return $response;
    }
    public function testAction(request $request)
    {
       //all the code for the user search function.
        $defaultData = array('user' => null );
        $search = $this->createFormBuilder($defaultData)
            ->add('user', 'entity_typeahead', array(
                    'class' => 'BFUserBundle:User',
                    'render' => 'username',
                    'route' => 'bf_site_search',
                    ))
            ->getForm(); 
        $search->handleRequest($request);
        if ($search->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

        return $this->render('BFSiteBundle:Home:search.html.twig', array(
              'search' => $search->createView(),
            )); 




    }

}
