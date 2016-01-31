<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;


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

    	$user = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findOneByUsername($username);
      $listFollows = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow')->findByFollowing($user);
    	$listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->listVideos($user);
      $listChallenges = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->listChallenges($user);
      $lastVideo = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->lastVideo($user);
      $listDuels = $user->getDuels();

      //retrieving the service
      $info = $this->container->get('bf_site.rankinfo');
      $rankinfo = $info->rankInfo($user);
      $duelRankInfo = $info->duelRankInfo($user);

      //calculating the age of the user.
      $interval = date_diff(new \Datetime(), $user->getBirthday());
      $age = $interval->y;

      //here we create an array with all the informations for the profileTop
      $numberfollows = count($listFollows);
      $numbervideos = count($listVideos);
      $duelwins = $user->getDuelWins();
      $profileTopInfo=array('followscount' => $numberfollows, 'videoscount' => $numbervideos, 'age' => $age,'duelwins' => $duelwins);


      $lists=array(  'listVideos' => $listVideos,'lastVideo' => $lastVideo,'listChallenges' => $listChallenges,'listFollows' => $listFollows,'listDuels' => $listDuels);
      $rank = array('rankinfo' => $rankinfo,'duelrankinfo' => $duelRankInfo);

    	return $this->render('BFSiteBundle:Profile:view.html.twig', array(
    	      'user' => $user,
            'rank' => $rank,
            'lists' => $lists,
            'follow' => $follow,
            'profiletop' => $profileTopInfo,
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
    public function changePasswordAction(request $request)
    { 
        $defaultData = array('oldpassword' => null, 'newpassword'=> null, 'newpasswordrepeat' => null);
        $form = $this->createFormBuilder($defaultData)
            ->add('oldpassword', 'password')
            ->add('newpassword', 'password')
            ->add('newpasswordrepeat', 'password')
            ->getForm(); 

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $user = $this->container->get('security.context')->getToken()->getUser();
            $cryptedpassword = $user->getPassword(); 
            if (password_verify($data['oldpassword'], $cryptedpassword)) {
              //the old password is correct. Check for the 2 passwords to be equal.
              if($data['newpassword'] == $data['newpasswordrepeat']){
                //2 passwords are ok. Update user.
                $userManager = $this->container->get('fos_user.user_manager');
                $user->setPlainPassword($data['newpassword']);
                $userManager->updateUser($user);
                $request->getSession()->getFlashBag()->add('success', 'Your password has been updated.');

                return $this->redirect($this->generateUrl('bf_site_settings'));
              }
              //passwords didn't match.
              $form->get('newpassword')->addError(new FormError('The new passwords have to match'));
              return $this->render('BFSiteBundle:Profile:settingsPassword.html.twig', array(
                'form' => $form->createView(),
              ));
  
            }
            //old password is not the good one.
            $form->get('oldpassword')->addError(new FormError('Invalid old password'));
            return $this->render('BFSiteBundle:Profile:settingsPassword.html.twig', array(
                'form' => $form->createView(),
              ));
  
        }

        return $this->render('BFSiteBundle:Profile:settingsPassword.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    public function changeUsernameAction(request $request)
    {

        //all the code for the user search function.
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();


        $defaultData = array('username' => $user->getUsername());
        $form = $this->createFormBuilder($defaultData)
            ->add('username', 'text')
            ->getForm(); 

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            if ($em->getRepository('BFUserBundle:User')->findOneByUsername($data['username']) === null) {
              $userManager = $this->container->get('fos_user.user_manager');
              $user->setUsername($data['username']);
              $userManager->updateUser($user);
              $request->getSession()->getFlashBag()->add('success', 'Your username has been updated.');
              return $this->redirect($this->generateUrl('bf_site_settings'));
            }
            //the username is already taken.
            $form->get('username')->addError(new FormError('This username is already taken. Please choose a new one.'));
        }

        return $this->render('BFSiteBundle:Profile:settingsUsername.html.twig', array(
          'form' => $form->createView(),
        ));
    }
}
