<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Challenge;
use BF\UserBundle\Entity\User;
use BF\SiteBundle\Entity\VideoRepository;
//les types
use BF\SiteBundle\Form\VideoType;
use BF\SiteBundle\Form\VideoEditType;
use BF\SiteBundle\Form\VideoDeleteType;
use BF\SiteBundle\Form\ChallengeType;

class VideoController extends Controller
{
    public function viewAction(request $request, $id)
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
            if($user == null){}
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }


        $em = $this->getDoctrine()->getManager();

	    // On récupère $id de la video
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if (null === $video) {
	      throw new NotFoundHttpException("La video n'existe pas.");
	    }

	    return $this->render('BFSiteBundle:Video:view.html.twig', array(
	      'video'           => $video,
	      'search' => $search->createView(),
	    ));
    }
    public function uploadAction(request $request, $id, $type)
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
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }


    	//we get the user entity
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	// On crée un objet Video
    	$video = new Video();
    	$video->setType($type);
    	//the upload is for a challenge video
    	if($type == 'challenge')
    	{
	    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
	    	$challenge = $repository->findOneBy(array('id' => $id));

	    	//we verify if the user already uploaded a video.
    		$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
    		$oldVideo = $repository->checkChallenge($user, $challenge);

    		if (null != $oldVideo) {
		            //The user alreday has a video in the directory.
		            $oldScore=$oldVideo->getScore();
		            $points = $user->getPoints() - $oldScore;
		            $user->setPoints($points);
		        }
		        else{
		            //this is the first video off the user.
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
			      if($video->getRepetitions() >= $six){$video->setScore('300');}
			      if($six > $video->getRepetitions() && $video->getRepetitions() >= $five){ $video->setScore('250');}
			      if($five > $video->getRepetitions() && $video->getRepetitions() >= $four){$video->setScore('200');}
			      if($four > $video->getRepetitions() && $video->getRepetitions() >= $three){$video->setScore('150');}
			      if($three > $video->getRepetitions() && $video->getRepetitions() >= $two){$video->setScore('100');}
			      if($two > $video->getRepetitions() && $video->getRepetitions() >= $one){$video->setScore('100');}
			      if($one > $video->getRepetitions()){$video->setScore('0');}
			      

			      //now we update the points of the user
			      $points = $video->getScore() + $user->getPoints();
			      $user->setPoints($points);
			      $em->persist($user);
			      $em->persist($video);
			      $em->flush();

			      $this->addFlash('success', 'Your video was uploaded to our servers and you received '.$video->getScore().' points for this video.');

			      return $this->redirect($this->generateUrl('bf_site_video', array('id' => $video->getId())));
			    }

		    return $this->render('BFSiteBundle:Video:upload.html.twig', array(
		      'form' => $form->createView(),
		      'search' => $search->createView(),
		    ));
    	}
    	//the upload is for a duel video
    	if($type == 'duel')
    	{
    		//we check if the user has the right to upload his video for this duel. (if it is his duel)
	    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Duel');
	    	$duel = $repository->findOneBy(array('id' => $id));
	    	if($duel->getHost() == $user->getUsername() OR $duel->getGuest() == $user->getUsername()){ //We verify if the user and duel correspond
	    			$video
			    	->setDate(new \Datetime())
			    	->setDuel($duel)
			    	->setUser($user)
			    	->setScore('0')
			    	;
			    	//the user is the guest for the duel
			    	if($duel->getGuest() == $user->getUsername()){ 
			    		//check if he already uploaded a file. In that case we redirect him to the challenges page.
			    		if($duel->getGuestCompleted() == '1'){
			    			$this->addFlash('warning','You already uploaded a video for this duel. You can only upload one video/duel.');
			       			return $this->redirectToRoute('bf_site_profile_duels');
			    		}
			    		else{
			    			$duel->setGuestCompleted('1');
			    		}	    		
			    	}
			    	//the user is the Host for the duel
		    		if($duel->getHost() == $user->getUsername()){ 
		    			//check if he already uploaded a file. In that case we redirect him to the challenges page.
		    			if($duel->getHostCompleted() == '1'){
			    			$this->addFlash('warning','You already uploaded a video for this duel. You can only upload one video/duel.');
			       			return $this->redirectToRoute('bf_site_profile_duels');
			    		}
			    		else{
			    			$duel->setHostCompleted('1');
			    		}
		    		}

		    		

			    	$form = $this->get('form.factory')->create(new VideoType, $video);
			    	if ($form->handleRequest($request)->isValid()) {
					    $em = $this->getDoctrine()->getManager();

					    $host = $duel->getHost();
		    			$guest = $duel->getGuest();

					    if($duel->getHostCompleted() == '1' && $duel->getGuestCompleted() == '1'){
			    			//both the players uploaded their video. We can now set the complete off the duel to 1
			    			$duel->setCompleted('1');
			    			//now we look at the video with the highest repitions and we give 50 points to the winner.
			    			$videos = $duel->getVideos();
			    			//we get the repetitions for each video
			    			foreach ($videos as $video) {
			    				$score = $video->getRepetitions();
					    		if($video->getUser() != $guest)
					    			{ $hostscore = $score;}
					    		elseif($video->getUser() == $guest)
					    			{ $guestscore = $score; }
					    		else{}
					    	}
					    	//we get compare the host to the guest score
						    if($hostscore > $guestscore){//host wins
						    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    							$host = $repository->findOneByUsername($host);
    							$guest = $repository->findOneByUsername($guest);

    							$points = $host->getPoints() + 50;
			      				$host->setPoints($points);
			      				$em->persist($host);

			      				//notifications
			      					//host
			      					$notificationhost = new Notification();
							    	$notificationhost
							    		->setDate(new \Datetime())
							    		->setMessage('Congratulations you won the duel against '.$guest->getUsername().' adn you received 50 points !')
							    		->setUser($host)
							    		->setWatched('0')
							    		->setDuel($duel)
		   							;
			      					//guest
		   							$notificationguest = new Notification();
							    	$notificationguest
							    		->setDate(new \Datetime())
							    		->setMessage('unfortunately you lost the duel against '.$host->getUsername())
							    		->setUser($guest)
							    		->setWatched('0')
							    		->setDuel($duel)
							    	;
			      				$em->persist($notificationhost);
			      				$em->persist($notificationguest);
						    }
						    elseif($hostscore < $guestscore){//guest wins
						    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    							$guest = $repository->findOneByUsername($guest);
    							$guest = $repository->findOneByUsername($host);

    							$points = $guest->getPoints() + 50;
			      				$guest->setPoints($points);
			      				$em->persist($guest);

			      				//notifications
			      					//guest
			      					$notificationguest = new Notification();
							    	$notificationguest
							    		->setDate(new \Datetime())
							    		->setMessage('Congratulations you won the duel against '.$host->getUsername().' and you received 50 points !')
							    		->setUser($guest)
							    		->setWatched('0')
							    		->setDuel($duel)
		   							;
			      					//host
		   							$notificationhost = new Notification();
							    	$notificationhost
							    		->setDate(new \Datetime())
							    		->setMessage('unfortunately you lost the duel against '.$guest->getUsername())
							    		->setUser($host)
							    		->setWatched('0')
							    		->setDuel($duel)
							    	;
			      				$em->persist($notificationhost);
			      				$em->persist($notificationguest);

						    }
						    elseif($hostscore == $guestscore){//same score,each 25 points
						    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    							$guest = $repository->findOneByUsername($guest);
    							$host = $repository->findOneByUsername($host);

    							//host points
    							$points = $host->getPoints() + 25;
			      				$host->setPoints($points);
			      				$em->persist($host);
			      				//guest points
			      				$points = $guest->getPoints() + 25;
			      				$guest->setPoints($points);
			      				$em->persist($guest);

			      				//notifications
			      					//guest
			      					$notificationguest = new Notification();
							    	$notificationguest
							    		->setDate(new \Datetime())
							    		->setMessage('Congratulations, the duel against '.$host->getUsername().' was a tie and you received 25 points !')
							    		->setUser($guest)
							    		->setWatched('0')
							    		->setDuel($duel)
		   							;
			      					//host
		   							$notificationhost = new Notification();
							    	$notificationhost
							    		->setDate(new \Datetime())
							    		->setMessage('Congratulations, the duel against '.$guest->getUsername().' was a tie and you received 25 points !')
							    		->setUser($host)
							    		->setWatched('0')
							    		->setDuel($duel)
							    	;
			      				$em->persist($notificationhost);
			      				$em->persist($notificationguest);
						    }
			    		}
					    //now we update the points of the user
					    $em->persist($video);
					    $em->persist($duel);
					    $em->flush();

					    $this->addFlash('success', 'Your video was uploaded to our servers.');

					    return $this->redirect($this->generateUrl('bf_site_video', array('id' => $video->getId())));
					    }

				    return $this->render('BFSiteBundle:Video:upload.html.twig', array(
				      'form' => $form->createView(),
				      'search' => $search->createView(),
				    ));
		    	}

	    	else{
	    		$this->addFlash('warning','You are not allowed to post a video to this Duel because it is not your duel');
	       		return $this->redirectToRoute('bf_site_challenges');
	    	}
		}
		if( $type == 'freestyle'){
			
		} 
    }
    public function deleteAction(request $request, $id)
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
        
        if ($search->handleRequest($request)->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $search->getData();
            $user = $data['user'];
            $username = $user->getUsername();
            return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $username)));
        }

	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video== null) {
	      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $check = $video->getUser();
	    if ($check != $user) {
	      throw $this->createNotFoundException("You can't delete a video that isn't yours");
	    }
	    
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
	    // Cela permet de protéger la suppression d'annonce contre cette faille
	    $form = $this->get('form.factory')->create(new VideoDeleteType, $video);

	    if ($form->handleRequest($request)->isValid()) {


	      $points =  $user->getPoints() - $video->getScore();
      	  $user->setPoints($points);
      	  $em->persist($user);
	      $em->remove($video);
	      $em->flush();

	      $request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");

	      return $this->redirect($this->generateUrl('bf_site_videos'));
	    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:delete.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView(),
		      'search' => $search->createView(),
		    ));
    }
    public function modifyAction(request $request, $id)
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

	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video== null) {
	      throw $this->createNotFoundException("The video n°".$id." doesn't exist.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $check = $video->getUser();
	    if ($check != $user) {
	      throw $this->createNotFoundException("You can't modify a video that isn't yours");
	    }
	    
		    $form = $this->get('form.factory')->create(new VideoEditType, $video);
		    if ($form->handleRequest($request)->isValid()) {

		      $em->persist($video);
		      $em->flush();

		      $request->getSession()->getFlashBag()->add('success', "Your video has been modified.");

		      return $this->redirect($this->generateUrl('bf_site_videos'));
		    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:modify.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView(),
		      'search' => $search->createView(),
		    ));
    }
}
