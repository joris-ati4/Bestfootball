<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Report;
//les types
use BF\SiteBundle\Form\Type\VideoType;
use BF\SiteBundle\Form\Type\VideoEditType;
use BF\SiteBundle\Form\Type\VideoDuelType;
use BF\SiteBundle\Form\Type\VideoDeleteType;
use BF\SiteBundle\Form\Type\VideoFreestyleType;
use BF\SiteBundle\Form\Type\ReportType;

class VideoController extends Controller
{
    public function viewAction(request $request, $code)
    {
        $em = $this->getDoctrine()->getManager();
	    // On récupère $id de la video
	    $video = $em->getRepository('BFSiteBundle:Video')->findOneByCode($code);
        //checking if the user is following the current user


	    if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
	    	//the user is connected
	    	$follower = $this->container->get('security.context')->getToken()->getUser();
		    $following = $em->getRepository('BFUserBundle:User')->findOneByUsername($video->getUser()->getUsername());
		    if($follower->getUsername() != $following->getUsername()){
		      $follow = $em->getRepository('BFSiteBundle:Follow')->checkFollow($follower, $following);
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
		    if($video->getType() == 'challenge'){
		    	$challenge = $video->getChallenge();
		    	$check = $em->getRepository('BFSiteBundle:Prediction')->checkPredict($follower, $challenge);
		    	if($check){
		    		$predict = null;
		    	}
		    	else{
		    		$predict = true;
		    	}
		    }
		    else{
		    	$predict = null;
		    }
	    }
	    else{
	    	$follow = null;
	    	$predict = null;
	    	$follower = null;
	    }
		    

	    

	    //retrieving the random videos through the service
	    //retrieving the service
      	$random = $this->container->get('bf_site.randomvideos');
      	$listVideos = $random->randomize($video);

      	//getting the comments
      	$listComments = $video->getComments();
      	$like = $em->getRepository('BFSiteBundle:Likes')->getLike($follower, $video);

	    if (null === $video) {
	      throw new NotFoundHttpException("La video n'existe pas.");
	    }
	    return $this->render('BFSiteBundle:Video:view.html.twig', array(
	      'like' => $like,
	      'video'  => $video,
	      'listVideos' => $listVideos,
	      'follow' => $follow,
	      'predict' => $predict,
	      'follower' => $follower,
	      'listComments' => $listComments,
	    ));
    }
    public function uploadAction(request $request, $id, $type)
    {
    	//we get the user entity
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	// On crée un objet Video
    	$video = new Video();
    	$video->setType($type);

    	//getting the code for the video
        $service = $this->container->get('bf_site.randomcode');
        $code = $service->generate('video');
        $video->setCode($code);



    	//the upload is for a challenge video
    	if($type == 'challenge')
    	{
	    	$challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->findOneBy(array('id' => $id));

	    	//we verify if the user already uploaded a video.
    		$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
    		$oldVideo = $repository->checkChallenge($user, $challenge);

    		if (null !== $oldVideo) {
		            //The user alreday has a video in the directory.
		            $oldScore=$oldVideo->getScore();
		            $points = $user->getPoints() - $oldScore;
		            $user->setPoints($points);
		    }

	    	$video
	    	->setDate(new \Datetime())
	    	->setUser($user)
	    	->setChallenge($challenge);
	    	;

	    	$form = $this->get('form.factory')->create(new VideoType, $video);
	    
		    if ($form->handleRequest($request)->isValid()) {

			    $em = $this->getDoctrine()->getManager();

		        $service = $this->container->get('bf_admin.videopoints');
        		$service->videoPoints($video);
	
			    //we convert the video to the right size and with the watermark
			    $exploded = explode('/', $video->getSource());
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, 'http://v.bestfootball.fr/test/convert.php?file='.$exploded[3]);
				$retour = curl_exec($curl);
				curl_close($curl);

				//sending a mail to all lower repetitions
				//$challengeMailer = $this->container->get('bf_site.challengeMailer');
      			//$result = $challengeMailer->ChallengeMail($video, $video->getChallenge());

			    $this->addFlash('success', 'Your video was uploaded to our servers and you received '.$video->getScore().' points for this video.');

			    return $this->redirect($this->generateUrl('bf_site_videos'));
			}

		    return $this->render('BFSiteBundle:Video:upload.html.twig', array(
		      'form' => $form->createView(),
		    ));
    	}
    	//the upload is for a duel video
    	if($type == 'duel')
    	{
    		//we check if the user has the right to upload his video for this duel. (if it is his duel)
	    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Duel');
	    	$duel = $repository->findOneBy(array('id' => $id));
	    	if($duel->getHost() == $user || $duel->getGuest() == $user){ //We verify if the user and duel correspond
	    			$video
			    	->setDate(new \Datetime())
			    	->setDuel($duel)
			    	->setUser($user)
			    	->setScore('0')
			    	;
			    	//the user is the guest for the duel
			    	if($duel->getGuest() == $user){ 
			    		//check if he already uploaded a file. In that case we redirect him to the challenges page.
			    		if($duel->getGuestCompleted() == '1'){
			    			$this->addFlash('warning','You already uploaded a video for this duel. You can only upload one video/duel.');
			       			return $this->redirectToRoute('bf_site_profile_duels');
			    		}
			    		else{
			    			$duel->setGuestCompleted('1');
			    			$userRole = 'guest';
			    		}	    		
			    	}
			    	//the user is the Host for the duel
		    		if($duel->getHost() == $user){ 
		    			//check if he already uploaded a file. In that case we redirect him to the challenges page.
		    			if($duel->getHostCompleted() == '1'){
			    			$this->addFlash('warning','You already uploaded a video for this duel. You can only upload one video/duel.');
			       			return $this->redirectToRoute('bf_site_profile_duels');
			    		}
			    		else{
			    			$duel->setHostCompleted('1');
			    			$userRole = 'host';
			    		}
		    		}

		    	//we check if the user has a video for this challenge. 
	    		$highVideo = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->highestVideo($user, $duel->getChallenge());
	    	
			    	$form = $this->get('form.factory')->create(new VideoDuelType, $video);
			    	if ($form->handleRequest($request)->isValid()) {
					    $em = $this->getDoctrine()->getManager();

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

						//we convert the video to the right size and with the watermark
				      $exploded = explode('/', $video->getSource());
					  $curl = curl_init();
					  curl_setopt($curl, CURLOPT_URL, 'http://v.bestfootball.fr/test/convert.php?file='.$exploded[3]);
					  $retour = curl_exec($curl);
					  curl_close($curl);

					    $this->addFlash('success', 'Your video was uploaded to our servers.');

					    return $this->redirect($this->generateUrl('bf_site_videos'));
					}

				    return $this->render('BFSiteBundle:Video:uploadDuel.html.twig', array(
				      'form' => $form->createView(),
				      'highVideo' => $highVideo,
				      'duel' => $duel,
				    ));
		    }

	    	else{
	    		$this->addFlash('warning','You are not allowed to post a video to this Duel because it is not your duel');
	       		return $this->redirectToRoute('bf_site_challenges');
	    	}
		}
		if( $type == 'freestyle')
		{
			$video
			    ->setDate(new \Datetime())
				->setUser($user)
			    ->setScore('0')
			    ->setRepetitions(0)
			    ;
			//frestyle section upload
			$form = $this->get('form.factory')->create(new VideoFreestyleType, $video);
	    
		    if ($form->handleRequest($request)->isValid()) {
			      $em = $this->getDoctrine()->getManager();
			      $em->persist($video);
			      $em->flush();

			      //we convert the video to the right size and with the watermark
			      $exploded = explode('/', $video->getSource());
				  $curl = curl_init();
				  curl_setopt($curl, CURLOPT_URL, 'http://v.bestfootball.fr/test/convert.php?file='.$exploded[3]);
				  $retour = curl_exec($curl);
				  curl_close($curl);

			      $this->addFlash('success', 'Your video was uploaded to our servers.');

			      return $this->redirect($this->generateUrl('bf_site_videos'));
			    }

		    return $this->render('BFSiteBundle:Video:uploadFreestyle.html.twig', array(
		      'form' => $form->createView(),
		    ));
		} 
    }
    public function deleteAction(request $request, $id)
    {

	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);
	    $user = $this->container->get('security.context')->getToken()->getUser();

	    if ($video === null) {
	      throw $this->createNotFoundException("This video doesn't exist.");
	    }

	    if ($video->getUser()->getid() != $user->getId()) {
	      throw $this->createNotFoundException("You can't delete a video that isn't yours");
	    }
	    
        $form = $this->get('form.factory')->create(new VideoDeleteType, $video);

        if ($form->handleRequest($request)->isValid()) {


        	//video is a duel video
        	if ($video->getType == 'duel') {
              	$request->getSession()->getFlashBag()->add('success', "You can't delete duel videos.");
		    	return $this->redirect($this->generateUrl('bf_site_videos'));
            }
            else{

	            $oldVideo = $video;


	            //deleting reports
	            $listReports = $video->getReports();
	            foreach ($listReports as $report) {
	                $em->remove($comment);
	            }
	            //deleting comments and quotes
	            $listComments = $video->getComments();
	            foreach ($listComments as $comment) {
	                $listQuotes = $comment->getQuotes();
	                    foreach ($listQuotes as $quote) {
	                      $em->remove($quote);
	                    }
	                $em->remove($comment);
	            }

	            //deleting likes
	            $listLikes = $video->getLikes();
	            foreach ($listLikes as $lik) {
	                $em->remove($lik);
	            }
	            $em->remove($video);
	            $em->flush();

	            if($oldVideo->getType() == 'challenge'){ //we need to update the users score
	                
	            //get all the videos of the user.
		      	$listVideos = $em->getRepository('BFSiteBundle:Video')->allVideos($user);

		      	//recount the points of the user
		      	$points = 0;
		      	$oldvideo = null;
		      	foreach ( $listVideos as $video) {
			        //compter les likes pour la vidéo.
			        $likePoints = count($video->getLikes()) * 5; // 5 points par like.
			        $points = $points + $likePoints;

			        //compter les points de la vidéo et éventuellement les 20 points d'entraînement
			        if($oldvideo === null){
			          $points = $points + $video->getScore();
			        }
			        elseif($oldvideo->getChallenge()->getId() == $video->getChallenge()->getId()){ //It's the same challenge.
			          //Look for 20 points
			          if($video->getScore() < $oldvideo->getScore()){
			            //give 20 points for improvement.
			            $points = $points + 20;
			          }
			        }
			        elseif($oldvideo->getChallenge()->getId() != $video->getChallenge()->getId()){ //It's a new challenge.
			         $points = $points + $video->getScore();
			        }

			        $oldvideo = $video;
			    }
      
 
	            }
	            
	            $user->setPoints($points);
	            $em->persist($user);
	            $em->flush();

				$request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");
				return $this->redirect($this->generateUrl('bf_site_videos'));
	    	}
		}
		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:delete.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView(),
		    ));
    }
    public function modifyAction(request $request, $id)
    {
	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video === null) {
	      throw $this->createNotFoundException("The video n°".$id." doesn't exist.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $check = $video->getUser();
	    if ($check != $user) {
	      throw $this->createNotFoundException("You can't modify a video that isn't yours");
	    }
	    
		    $form = $this->get('form.factory')->create(new VideoEditType, $video);
		    if ($form->handleRequest($request)->isValid()) {


		    	$service = $this->container->get('bf_admin.videopoints');
                $service->videoPoints($video);

		      $request->getSession()->getFlashBag()->add('success', "Your video has been modified.");

		      return $this->redirect($this->generateUrl('bf_site_videos'));
		    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:modify.html.twig', array('video' => $video,'form'   => $form->createView()));
    }
    public function reportAction(request $request, $id)
    {
	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video === null) {
	      throw $this->createNotFoundException("The video n°".$id." doesn't exist.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $report = new Report();
	    $report
	    	->setVideo($video)
	    	->setUser($user)
	    	->setDate(new \Datetime())
	    	->setTreated(0)
	    ;

		    $form = $this->get('form.factory')->create(new ReportType, $report);
		    if ($form->handleRequest($request)->isValid()) {

		      $em->persist($report);
		      $em->flush();
		      $request->getSession()->getFlashBag()->add('success', "The video has been reported and will be reviewed by our Admins.");
		      return $this->redirect($this->generateUrl('bf_site_videos'));
		    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:report.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView(),
		    ));
    }
    public function freestyleAction()
    {
	    $em = $this->getDoctrine()->getManager();
	    $listFreestyles = $em->getRepository('BFSiteBundle:Video')->findByType('freestyle');
	    //choosing a random video.
	    $i = array_rand($listFreestyles, 1);
	    $video = $listFreestyles[$i];
	    $listVideos = array();

	    //selecting 12 random videos to display under the headsection of the page.
	    if(count($listFreestyles) > 12){
	    	//select 12 random videos.
	    	$i = array_rand($listFreestyles, 12);
            for($j = 0; $j < $k; $j++){
                $index = $i[$j];
                $object = $listFreestyles[$index];
                array_push($listVideos, $object);
            }
	    }
	    else{
	    	//put all the videos into the video list.
	    	$listVideos = $listFreestyles;
	    }

	    return $this->render('BFSiteBundle:Video:freestyle.html.twig', array(
	      'video' => $video,
	      'listVideos'   => $listVideos,
	    ));
    }
    public function twitterAction($code)
    {
    	$em = $this->getDoctrine()->getManager();
    	$video = $em->getRepository('BFSiteBundle:Video')->findOneByCode($code);
	   
	    return $this->render('BFSiteBundle:Video:twitter.html.twig', array(
	      'video' => $video
	    ));
    }
}
