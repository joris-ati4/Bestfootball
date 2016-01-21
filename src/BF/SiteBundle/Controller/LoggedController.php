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
        
        //retrieving the service
        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);
        
        return $this->render('BFSiteBundle:Home:logged.html.twig', array(
          'listVideos' => $wallArray,
          'listDuels' => $listDuels,
          'listNotifications' => $listNotifications,
          'user' => $user,
          'rankinfo' => $rankinfo,
          'duelrankinfo' => $duelRankInfo,
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
