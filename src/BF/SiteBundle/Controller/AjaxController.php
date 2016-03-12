<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
Symfony\Component\HttpFoundation\RedirectResponse;
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Prediction;

class AjaxController extends Controller
{
    public function userInfoAction(request $request)
    {
        $username = $request->get('username');
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $user = $repository->findOneByUsername($username);

        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);

        $rank = array('rankinfo' => $rankinfo, 'duelrankinfo' => $duelRankInfo);

        return $this->render('BFSiteBundle:Userinfo:userinfo.html.twig', array(
            'rank' => $rank,
            'user' => $user,
            ));
    }
    public function getChallengeAction(request $request)
    {
        $id = $request->get('id');
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);

        return $this->render('BFSiteBundle:Challenge:challengeAjax.html.twig', array(
            'challenge' => $challenge,
            ));
    }
    public function notifreadAction(request $request)
    {
        $em =$this->getDoctrine()->getManager();
        $id = $request->get('id');
        $notification = $em ->getRepository('BFSiteBundle:Notification')->find($id);

        $notification->setWatched('1');
        $em->persist($notification);
        $em->flush();

       return new response();
    }
    public function checkUsernameAction(request $request)
    {
        $username = $request->get('username');
        $user = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findOneByUsername($username);
        

        if(!$user){
            //username is available
            $response = new response('ok');
        }
        else{
            //username is not available
            $response = new response('used');
        }
        return $response; 
    }
    public function duelCopyVideoAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        //get the duel.
        $duelId = $request->get('duel');
        $duel = $em->getRepository('BFSiteBundle:Duel')->find($duelId);
        //get the video that has to be duplicated
        $Usevideo = $em->getRepository('BFSiteBundle:Video')->find($request->get('video'));

       
           
                   
        if($duel->getHost() == $user){ 
            $duel->setHostCompleted('1');
            $userRole = 'host';
        }
        else{
            $duel->setGuestCompleted('1');
            $userRole = 'guest';       
        }


        $video = new Video();
        $video
            ->setDate(new \Datetime())
            ->setDuel($duel)
            ->setUser($user)
            ->setScore('0')
            ->setType('duel')
            ->setRepetitions($Usevideo->getRepetitions())
            ->setSource($Usevideo->getSource())
            ->setThumbUrl('jpg')
            ->setThumbAlt('Thumbnail of '.$user->getUsername().' for the '.$duel->getChallenge()->getTitle().' duel.')
            ->setTitle($Usevideo->getTitle())
            ;

            
        $host = $duel->getHost();
        $guest = $duel->getGuest();


                        if($duel->getHostCompleted() == 1 && $duel->getGuestCompleted() == 1)
                        {
                            //both the players uploaded their video. We can now set the complete off the duel to 1
                            $duel->setCompleted('1');
                            //now we look at the video with the highest repitions and we give 50 points to the winner.
                            //get the video of the other player
                            if($userRole == 'host'){
                                $otherVideo = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->duelGuestVideo($guest,$duel);
                                $hostscore = $video->getRepetitions();
                                $guestscore = $otherVideo->getRepetitions();
                            }
                            elseif($userRole == 'guest'){
                                $otherVideo = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->duelHostVideo($host,$duel);
                                $guestscore = $video->getRepetitions();
                                $hostscore = $otherVideo->getRepetitions();
                            }
                            
                            //we get compare the host to the guest score
                            if($hostscore > $guestscore){//host wins
                                
                                $points = $host->getDuelPoints() + 100;
                                $host->setDuelPoints($points);
                                $wins = $host->getDuelWins() + 1;
                                $host->setDuelWins($wins);
                                $duel->setWinner($host);
                                $em->persist($duel);
                                $em->persist($host);

                                //notifications
                                $link = $this->generateUrl('bf_site_duel_view', array('id' => $duel->getId()));
                                //host
                                $message = 'Congratulations you won the duel against '.$guest->getUsername().' adn you received 100 points !';
                                $service = $this->container->get('bf_site.notification');
                                $notificationhost = $service->create($host, $message, $duel, $link);
                                //guest
                                $message = 'unfortunately you lost the duel against '.$host->getUsername();
                                $service = $this->container->get('bf_site.notification');
                                $notificationguest = $service->create($guest, $message, $duel, $link);
                                $em->persist($notificationhost);
                                $em->persist($notificationguest);
                            }
                            elseif($hostscore < $guestscore){//guest wins

                                $points = $guest->getDuelPoints() + 100;
                                $guest->setDuelPoints($points);
                                $wins = $guest->getDuelWins() + 1;
                                $guest->setDuelWins($wins);
                                $duel->setWinner($guest);
                                $em->persist($duel);
                                $em->persist($guest);

                                //notifications
                                $link = $this->generateUrl('bf_site_duel_view', array('id' => $duel->getId()));
                                    //guest
                                    $message = 'Congratulations you won the duel against '.$host->getUsername().' adn you received 100 points !';
                                    $service = $this->container->get('bf_site.notification');
                                    $notificationguest = $service->create($guest, $message, $duel, $link);
                                    //host
                                    $message = 'unfortunately you lost the duel against '.$guest->getUsername();
                                    $service = $this->container->get('bf_site.notification');
                                    $notificationhost = $service->create($host, $message, $duel, $link);

                                $em->persist($notificationhost);
                                $em->persist($notificationguest);

                            }
                            elseif($hostscore == $guestscore){//same score,each 50 points

                                //host points
                                $points = $host->getDuelPoints() + 50;
                                $host->setDuelPoints($points);
                                $em->persist($host);
                                //guest points
                                $points = $guest->getDuelPoints() + 50;
                                $guest->setDuelPoints($points);
                                $em->persist($guest);

                                //notifications
                                    $link = $this->generateUrl('bf_site_duel_view', array('id' => $duel->getId()));
                                    //host
                                    $message = 'Congratulations, the duel against '.$guest->getUsername().' was a tie and you received 50 points !';
                                    $service = $this->container->get('bf_site.notification');
                                    $notificationhost = $service->create($host, $message, $duel, $link);
                                    //guest
                                    $message = 'Congratulations, the duel against '.$host->getUsername().' was a tie and you received 50 points !';
                                    $service = $this->container->get('bf_site.notification');
                                    $notificationguest = $service->create($guest, $message, $duel, $link);
                                    $em->persist($notificationhost);
                                    $em->persist($notificationguest);
                            }
                        }
                        //now we update the points of the user
                        $em->persist($video);
                        $em->persist($duel);
                        $em->flush();

                        $this->addFlash('success', 'Your video was uploaded to our servers.');

                        return new response();


    }
    public function predictAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $voterId = $request->get('voterId');
        $predidtionedId = $request->get('predictionedId');
        $challengeId = $request->get('challengeId');

        $voter = $em->getRepository('BFUserBundle:User')->find($voterId);
        $predictioned = $em->getRepository('BFUserBundle:User')->find($predidtionedId);
        $challenge = $em->getRepository('BFSiteBundle:Challenge')->find($challengeId);

        $check = $em->getRepository('BFSiteBundle:Prediction')->checkPredict($voter, $challenge);

        //check if the user already predicted for this challenge.
        if($check){
            $this->addFlash('warning', 'You already voted for this challenge. You can only vote once for every challenge.');
        }
        else{
            $prediction = new Prediction();
            $prediction
                ->setDate(new \Datetime())
                ->setVoter($voter)
                ->setPredictioned($predictioned)
                ->setChallenge($challenge)
            ;

            //create a notification for the predictioned user.
            $message = 'Congratulations, '.$voter->getUsername().' predicted that you will win the '.$challenge->getTitle().' challenge at the end of the season.';
            $link = $this->generateUrl('bf_site_profile', array('username' => $voter->getUsername()));
            $service = $this->container->get('bf_site.notification');
            $notification = $service->create($predictioned, $message, null, $link);       

            $em->persist($prediction);
            $em->persist($notification);
            $em->flush();

            $this->addFlash('success', 'Thank you for your prediction and good chance.');
        }

        return new response();
    }
    public function setLocaleAction($language = null)
    {
        if($language != null)
        {
            // On enregistre la langue en session
            $this->get('session')->set('_locale', $language);
        }
     
        // on tente de rediriger vers la page d'origine
        $url = $this->container->get('request')->headers->get('referer');
        if(empty($url))
        {
            $url = $this->container->get('router')->generate('bf_site_homepage');
        }
     
        return new RedirectResponse($url);
    }


 
}
