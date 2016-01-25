<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggedController extends Controller
{
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

        //we get users the user is following.
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
        $listFollows = $repository->findByFollower($user);
        $numberfollowings = count($listFollows);

        if($numberfollowings > 5){
            $k = 5;
        }
        else{
            $k = $numberfollowings;
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $lastVideos = $repository->latestVideos();

        //retrieve 5 followings and get the videos of them.
        $listVideosFollows =array();
        $i = array_rand($listFollows, $k);
        for($j=0;$j<$k;$j++){
            $following = $listFollows[$i[$j]]->getFollowing();
            $listVideos = $repository->latestFollowingVideos($following);
            $object=array('user' => $following, 'listVideos' => $listVideos);
            array_push($listVideosFollows, 'object' => $object);
        }

        //retrieving the service
        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);
        $rank=array("rankinfo" => $rankinfo, "duelrankinfo" => $duelRankInfo);
        $lists = array('lastVideos' => $lastVideos,'listNotifications' => $listNotifications,'listFollows' => $listFollows, 'listVideosFollows' => $listVideosFollows);

        //calculating the age of the user.
        $birthday = $user->getBirthday();
        $now = new \Datetime();
        $interval = date_diff($now, $birthday);
        $age = $interval->y;
        
        return $this->render('BFSiteBundle:Home:logged.html.twig', array(
          'lists' => $lists,
          'user' => $user,
          'age' => $age,
          'rank' => $rank,
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
}
