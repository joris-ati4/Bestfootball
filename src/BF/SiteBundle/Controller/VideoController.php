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

	    	$one = $challenge->getOne();
	    	$two = $challenge->getTwo();
	    	$three = $challenge->getThree();
	    	$four = $challenge->getFour();
	    	$five = $challenge->getFive();
	    	$six = $challenge->getSix();

	    	$video
	    	->setDate(new \Datetime())
	    	->setUser($user)
	    	->setChallenge($challenge);
	    	;

	    	$form = $this->get('form.factory')->create(new VideoType, $video);
	    
		    if ($form->handleRequest($request)->isValid()) {
			      $em = $this->getDoctrine()->getManager();


			      //if the video is for an ambassador challenge
		        if($challenge->getType() != 'normal'){
		        	//we give the user 300 points
		        	$video->setScore('0');
		        }
		        else{
		        	//the video is for a normal challenge.
		        	if($video->getRepetitions() >= $six){$video->setScore('300');}
			      	if($six > $video->getRepetitions() && $video->getRepetitions() >= $five){ $video->setScore('250');}
			      	if($five > $video->getRepetitions() && $video->getRepetitions() >= $four){$video->setScore('200');}
			      	if($four > $video->getRepetitions() && $video->getRepetitions() >= $three){$video->setScore('150');}
			      	if($three > $video->getRepetitions() && $video->getRepetitions() >= $two){$video->setScore('100');}
			      	if($two > $video->getRepetitions() && $video->getRepetitions() >= $one){$video->setScore('50');}
			      	if($one > $video->getRepetitions()){$video->setScore('0');}
		        }
			
			    //now we update the points of the user
			    $points = $video->getScore() + $user->getPoints();
			    $user->setPoints($points);
			    $em->persist($user);
			    $em->persist($video);
			    $em->flush();

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
	    	    
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
	    // Cela permet de protéger la suppression d'annonce contre cette faille
	    $form = $this->get('form.factory')->create(new VideoDeleteType);

	    $form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {

	    	//vérifier s'il y a des autres videos pour ce challenge
	    	if($video->getType() == 'challenge'){
		    	//check if the video is the highest score.
		    	$challenge = $video->getChallenge();
		    	$highestVideo =  $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->highestVideo($user, $challenge);

		    	if($video->getId() == $highestVideo->getId()){ //the video that will be deleted is the highest video.
		    		//we delete the video and delete the score.
		    		$deleteVideoScore = $video->getScore();
		    		$userPoints = $user->getPoints();

		    		$points =  $userPoints - $deleteVideoScore;
		    		$user->setPoints($points);
			      	$em->persist($user);
		
		    		//we check if there is another video. and give this score to the user.
		    		$oldVideo = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->secondVideo($user, $challenge);

		    		if(isset($oldVideo)){ //there is a video before.

		    			$oldVideoScore = $oldVideo->getScore();
		    			$userPoints = $user->getPoints();
		    			$points =  $userPoints + $oldVideoScore;
		    			$user->setPoints($points);
			      		$em->persist($user);
		    		}

		    		//checking for comments. if there are, we delete them.
		    		if($video ->getComments() !== null){
		    			$comments = $video ->getComments();
		    			foreach ($comments as $comment){ 
			    			$em->remove($comment);
			    		}
		    		}
		    		$em->remove($video);
			      	$em->flush();
			      	$request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");
					return $this->redirect($this->generateUrl('bf_site_videos'));


		    	}
		    	else{ //if the video is not the highest score. We just delete it.
		    		//checking for comments. if there are, we delete them.
		    		if($video ->getComments() !== null){
		    			$comments = $video ->getComments();
		    			foreach ($comments as $comment){ 
			    			$em->remove($comment);
			    		}
		    		}
		    		$em->remove($video);
		    		$em->flush();
		    		$request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");
					return $this->redirect($this->generateUrl('bf_site_videos'));
				}  	   		
		    }
		    elseif($video->getType() == 'freestyle'){
		    	//freestyle videos can always be deleted.
		    	//checking for comments. if there are, we delete them.
		    		if($video ->getComments() !== null){
		    			$comments = $video ->getComments();
		    			foreach ($comments as $comment){ 
			    			$em->remove($comment);
			    		}
		    		}
		    	$em->remove($video);
		    	$em->flush();
		    	$request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");
	      		return $this->redirect($this->generateUrl('bf_site_videos'));
		    }
		    else{
		    	//duel videos can't be deleted.
		    	$request->getSession()->getFlashBag()->add('success', "You can't delete duel videos.");
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


		    	$service = $this->container->get('bf_site.videospoints');
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
