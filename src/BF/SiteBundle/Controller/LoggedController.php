<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggedController extends Controller
{
    public function loggedAction(request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $listNotifications = $user->getNotifications();
        $listFollows = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow')->findByFollowing($user);
        $lastVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->latestVideos();

        $numberfollows = count($listFollows);

        if($numberfollows > 5){
            $k = 5;
            $listVideosFollows =array();
            $i = array_rand($listFollows, $k);
            for($j = 0; $j < $k; $j++){
                $index = $i[$j];
                $following = $listFollows[$index]->getFollowing();
                $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->latestFollowingVideos($following);
                $object=array('user' => $following, 'listVideos' => $listVideos);
                array_push($listVideosFollows, $object);
            }
        }
        elseif(1 < $numberfollows && $numberfollows < 5 ){
            $k = $numberfollows;
            $listVideosFollows =array();
            $i = array_rand($listFollows, $k);
            for($j = 0; $j < $k; $j++){
                $index = $i[$j];
                $following = $listFollows[$index]->getFollowing();
                $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->latestFollowingVideos($following);
                $object=array('user' => $following, 'listVideos' => $listVideos);
                array_push($listVideosFollows, $object);
            }
        }
        elseif($numberfollows == 1){ //only 1 follower
            $listVideosFollows =array();
            $following = $listFollows[0]->getFollowing();
            $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->latestFollowingVideos($following);
            $object=array('user' => $following, 'listVideos' => $listVideos);
            array_push($listVideosFollows, $object);
        }
        else{ //no followers
            $listVideosFollows =array();
        }

        //retrieve 5 followings and get the videos of them.
        

        //retrieving the service
        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);
        $rank=array("rankinfo" => $rankinfo, "duelrankinfo" => $duelRankInfo);
        $lists = array('lastVideos' => $lastVideos,'listNotifications' => $listNotifications,'listFollows' => $listFollows, 'listVideosFollows' => $listVideosFollows);

        //calculating the age of the user.
        $interval = date_diff(new \Datetime(), $user->getBirthday());
        $age = $interval->y;

        //here we create an array with all the informations for the profileTop
        $listVideos = $user->getVideos();        
        $numbervideos = count($listVideos);
        $duelwins = $user->getDuelWins();
      
        $profileTopInfo=array('followscount' => $numberfollows, 'videoscount' => $numbervideos, 'age' => $age,'duelwins' => $duelwins);

        
        return $this->render('BFSiteBundle:Home:logged.html.twig', array(
          'lists' => $lists,
          'user' => $user,
          'age' => $age,
          'rank' => $rank,
          'profiletop' => $profileTopInfo,
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
    public function userSearchAction(request $request)
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
