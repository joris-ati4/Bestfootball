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
        


        $points = $user->getPoints();
          if( '0'<= $points && $points <= '1000'){$level = 'Unknown'; $percent=($points/1000)*100;$min=0;$max=1000;$style='progress-bar-success';} //incognito
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
}
