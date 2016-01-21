<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function indexAction(request $request)
    {
        //if the user is connected we redirect him to the logged page
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
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

        return $this->render('BFSiteBundle:Home:challengespartner.html.twig', array(
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
    public function rankingAction(request $request,$country,$state)
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


        $em = $this->getDoctrine()->getEntityManager();

        if($country == 'global'){ //the global ranking of all the users
            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $ranking = $repository->findBy(array(),array('points' => 'desc'));
            $rankingGirls =$repository->findBy(array('gender' => 'Female'),array('points' => 'desc'));
            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
            $listCountries = $repository->findall();
        }
        else{ 
            if($state == 'country'){//rankings for country
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $country = $repository->findOneByName($country);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->countryRanking($country);
                $rankingGirls =$repository->countryRankingGirls($country);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $listCountries = $repository->findall();
                $listStates = $country->getStates();
            }
            else{ //ranking for state
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:State');
                $state = $repository->findOneByName($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->stateRanking($state);
                $rankingGirls =$repository->stateRankingGirls($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $listCountries = $repository->findall();
            }
        }
        //rankings for state
            return $this->render('BFSiteBundle:Home:ranking.html.twig',array(
              'search' => $search->createView(),
              'ranking' => $ranking,
              'rankingGirls' => $rankingGirls,
              'listCountries' => $listCountries,
              'country' => $country,
              'state' => $state,
            ));
           
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

        //we get the last videos of the users the user is following.
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
        $listFollows = $repository->findByFollower($user);


        $wallArray = array();

        if($listFollows !== null ){
           
            foreach($listFollows as $follow)
            {
                $following = $follow->getFollowing();
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
                $listVideos = $repository->listHomeVideos($following);

                //creating an array of the list
                foreach($listVideos as $video)
                {
                    array_push($wallArray, $video);
                }

                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Duel');
                $listDuels = $repository->listDuelsComplete($following);
                foreach($listDuels as $duel)
                {
                    array_push($wallArray, $duel);
                }
                
            }
        }
        else{ //if the listFollows is null, we give the user the last 50 videos uploaded on the site.

        }
            
        //now we shuffle the array.
        shuffle($wallArray);
        


        $points = $user->getPoints();
          if( '0'<= $points && $points <= '1000'){$level = 'Uknown'; $percent=($points/1000)*100;$min=0;$max=1000;$style='progress-bar-success';} //incognito
          if( '1000'< $points && $points <= '2000'){$level = 'Promising Talent';$percent=(($points-1000)/1000)*100;$min=1000;$max=2000;$style='progress-bar-success';}
          if( '2000'< $points && $points <= '3500'){$level = 'Rising Star';$percent=(($points-2000)/1500)*100;$min=2000;$max=3500;$style='progress-bar-info';}
          if( '3500'< $points && $points <= '5999'){$level = 'Real Star';$percent=(($points-3500)/1499)*100;$min=3500;$max=5999;$style='progress-bar-warning';}
          if( '6000'<= $points){$level = 'Legend';$percent=(($points-6000)/2000)*100;$min=6000;$max=8000;$style='progress-bar-danger';}


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
          'listVideos' => $wallArray,
          'listDuels' => $listDuels,
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


        //the code for the proposition

         $data = array();
            $form = $this->createFormBuilder($data)
                ->add('name', 'text')
                ->add('email', 'email')
                ->add('reason', 'choice',
                    array('choices' => array(
                        'propose a challenge'   => 'propose a challenge',
                        'a problem with the site' => 'a problem with the site',
                        'Partnership'   => 'Partnership',
                    )))
                ->add('message', 'textarea')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            // $data is a simply array with your form fields 
            // like "query" and "category" as defined above.
            $data = $form->getData();
            $name = $data['name'];
            $email = $data['email'];
            $reason = $data['reason'];
            $text = $data['message'];
            $message = \Swift_Message::newInstance()
                    ->setSubject('A new challenge proposition')
                    ->setFrom('noreply@bestfootball.fr')
                    ->setTo('joris.hart@ezwebcreation.fr')
                    ->setBody(
                        $this->renderView(
                            // app/Resources/views/Emails/registration.html.twig
                            'Emails/contact.html.twig',
                            array('text' => $text, 'name' => $name, 'email' => $email, 'reason' => $reason)
                        ),
                        'text/html'
                    )
            ;
            $this->get('mailer')->send($message);
            $this->addFlash('success', 'Your message has been send. Thank you.');
            return $this->redirect($this->generateUrl('bf_site_homepage'));
        }

        return $this->render('BFSiteBundle:Home:contact.html.twig', array(
              'search' => $search->createView(),
              'form' => $form->createView(),
            ));
    }
}
