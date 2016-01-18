<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use BF\UserBundle\Entity\User;
use BF\SiteBundle\Entity\Picture;
//les types
use BF\UserBundle\Form\UserType;
use BF\UserBundle\Form\UserPictureType;
use BF\SiteBundle\Form\PictureType;
use BF\SiteBundle\Entity\Follow;


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

          if($follow == null ){
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
          var_dump($request->request->All());
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
