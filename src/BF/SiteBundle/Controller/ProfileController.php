<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


//les types
use BF\UserBundle\Form\Type\UserType;
use BF\UserBundle\Form\Type\UserPictureType;


class ProfileController extends Controller
{
    public function viewAction($username,request $request)
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

      //checking if the user is following the current user

        $follower = $this->container->get('security.context')->getToken()->getUser();

        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $following = $repository->findOneByUsername($username);

        if($follower->getUsername() != $following->getUsername()){ //the user is not viewing it's own profile page
          $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
          $follow = $repository->checkFollow($follower, $following);

          if($follow === null ){
            $follow = 0;
          }
          else{
            $follow =1;
          }
        }
        else{
          $follow = null;
        }

    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$user = $repository->findOneByUsername($username);
      $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
      $listFollows = $repository->findByFollowing($user);
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
    	$listVideos = $repository->listVideos($user);

      $listChallenges = $repository->listChallenges($user);
      $lastVideo = $repository->lastVideo($user);

      $listDuels = $user->getDuels();

      //here we get the rank + points of the user
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

        //calculating the age of the user.
        $birthday = $user->getBirthday();
        $now = new \Datetime();
        $interval = date_diff($now, $birthday);
        $age = $interval->y;
      

    		return $this->render('BFSiteBundle:Profile:view.html.twig', array(
    	      'user' => $user,
            'age' => $age,
    	      'listVideos' => $listVideos,
            'lastVideo' => $lastVideo,
            'listChallenges' => $listChallenges,
            'listFollows' => $listFollows,
            'listDuels' => $listDuels,
            'level' => $level,
            'percent' => $percent,
            'min' => $min,
            'max' => $max,
            'style' => $style,
            'ranking' => $ranking,
            'follow' => $follow,
            'search' => $search->createView(),
    	    ));
    }
    public function videosAction(request $request)
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
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $listVideos = $repository->findByUser($user);

        return $this->render('BFSiteBundle:Profile:videos.html.twig', array(
          'listVideos' => $listVideos,
          'search' => $search->createView(),
        ));
    }
    public function settingsAction(request $request)
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

        return $this->render('BFSiteBundle:Profile:settings.html.twig', array(
          'search' => $search->createView(),
        ));
    }
    public function settingsInfoAction(request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
      
        $form = $this->get('form.factory')->create(new UserType, $user);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($user);
          $em->flush();

          $request->getSession()->getFlashBag()->add('success', 'Your profile has been modified.');

          return $this->redirect($this->generateUrl('bf_site_settings'));
        }

        return $this->render('BFSiteBundle:Profile:settingsInfo.html.twig', array(
          'form' => $form->createView(),
          'user' => $user,
        ));
    }
    public function settingsPictureAction(request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->get('form.factory')->create(new UserPictureType, $user);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($user);
          $em->flush();

          $request->getSession()->getFlashBag()->add('success', 'Your profile Picture has been updated.');

          return $this->redirect($this->generateUrl('bf_site_settings'));
        }

        return $this->render('BFSiteBundle:Profile:settingsPicture.html.twig', array(
          'form' => $form->createView(),
          'user' => $user,
        ));
    }
}
