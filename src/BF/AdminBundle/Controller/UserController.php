<?php

namespace BF\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//Les entités
use BF\UserBundle\Entity\User;
//les types

class UserController extends Controller
{
    public function usersAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$listUsers = $repository->findall();
	    return $this->render('BFAdminBundle:User:index.html.twig',array(
	    	'listUsers' => $listUsers,
	    	));
    }
    public function userViewAction($id)
    {
        $user = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->find($id);

        $numberfollows = count($listFollows = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow')->findByFollowing($user));
        $numbervideos = count($listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->listVideos($user));
        $duelwins = $user->getDuelWins();
        

        $allChallenges = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->findall();     
        $userChallenges = array();
        foreach ($allChallenges as $challenge) {
            //get the vidéos for every challenge
            $listChallengeVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->videoForChallenge($challenge, $user);
            if(!empty($listChallengeVideos)){
                array_push( $userChallenges, $listChallengeVideos);
            }
            
        }
        $listDuels = $user->getDuels();

        //retrieving the service
        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);

        //calculating the age of the user.
        $interval = date_diff(new \Datetime(), $user->getBirthday());
        $age = $interval->y;

        //here we create an array with all the informations for the profileTop
        $profileTopInfo=array('followscount' => $numberfollows, 'videoscount' => $numbervideos, 'age' => $age,'duelwins' => $duelwins);
        $lists=array('listVideos' => $listVideos, 'listFollows' => $listFollows,'listDuels' => $listDuels, 'userChallenges' => $userChallenges);
        $rank = array('rankinfo' => $rankinfo,'duelrankinfo' => $duelRankInfo);

        return $this->render('BFAdminBundle:User:view.html.twig',array(
            'lists' => $lists,
            'user' => $user,
            'rank' => $rank,
            'profiletop' => $profileTopInfo,
        ));
    }
    public function modUserAction(request $request, $id)
    {
        // On crée un objet Challenge
        $user = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->find($id);;

        $form = $this->get('form.factory')->create(new ChallengeEditType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

          return $this->redirect($this->generateUrl('bf_site_admin_challenges'));
        }

        return $this->render('BFAdminBundle:Admin:addChallenge.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    public function delUserAction(request $request, $id)
    {
        // On crée un objet Challenge
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);;

        $form = $this->get('form.factory')->create(new ChallengeDelType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();

        $listVideos = $challenge->getVideos();
        foreach ($listVideos as $video) {
            $video
                ->setChallenge(NULL)
                ->setType('freestyle')
            ;
            $em->persist($video);
        }


          $em->remove($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'the challenge was deleted');

          return $this->redirect($this->generateUrl('bf_site_admin_challenges'));
        }

        return $this->render('BFAdminBundle:Admin:delChallenge.html.twig', array(
          'form' => $form->createView(),
          'challenge' => $challenge,
        ));
    }
    public function whoIsOnlineAction()
    {
        $users = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->getActive();

        return $this->render('BFAdminBundle:User:onlineList.html.twig',array(
            'users' => $users,
        ));
    }
}
