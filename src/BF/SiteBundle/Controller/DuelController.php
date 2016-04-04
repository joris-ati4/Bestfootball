<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Duel;
//les types
use BF\SiteBundle\Form\Type\DuelType;
use BF\SiteBundle\Form\Type\DuelFRType;

class DuelController extends Controller
{
    public function viewAction($code,request $request)
    {   
        $em = $this->getDoctrine()->getManager();
	    $duel = $em->getRepository('BFSiteBundle:Duel')->findOneByCode($code);
	    if (null === $duel) {
	      throw new NotFoundHttpException("This duel doesn't exist");
	    }
        $host = $duel->getHost();

        if($duel->getCompleted() == 1){
            //make 2 variables, one for the loser and one for the winner.
            $videoHost = $em->getRepository('BFSiteBundle:Video')->duelHostVideo($host, $duel);
            $videoGuest= $em->getRepository('BFSiteBundle:Video')->duelGuestVideo($duel->getGuest(), $duel);
        }
        else{
            $videoHost = null;
            $videoGuest = null;
        }

      	return $this->render('BFSiteBundle:Duel:view.html.twig', array(
	      	'duel'           => $duel,
            'videohost' => $videoHost,
            'videoguest' => $videoGuest,
	    	));
    }
    public function createAction(request $request, $username)
    {
    	$em = $this->getDoctrine()->getManager();
    	//we get the host and the invited user
    	$host = $this->container->get('security.context')->getToken()->getUser();
    	$guest = $em->getRepository('BFUserBundle:User')->findOneByUsername($username);
    	//we check that the host and guest are not the same person
    	if($host == $guest){
    		throw new NotFoundHttpException("You can't create a duel against yourself.");
    	}
    	//we create a new duel and set the users to the duel
		$date = new \Datetime();
		  $duration = (7 * 24 * 60 * 60);
		  $endtimestamp = $date->getTimestamp() + $duration;
		  $date->setTimestamp($endtimestamp);
    	$duel = new Duel();
    	$duel ->setBeginDate(new \Datetime())
              ->setEndDate($date)
              ->setAccepted('0')
              ->setCompleted('0')
              ->setHostCompleted('0')
              ->setGuestCompleted('0')
              ->setHost($host)
              ->setGuest($guest)
              ->setType('duel')
              ->addUser($host)
              ->addUser($guest)
        ;
        //getting the code for the duel
        $service = $this->container->get('bf_site.randomcode');
        $code = $service->generate('duel');
        $duel->setCode($code);

    	$message = 'You received an invitation for a duel from '.$host->getUsername();
        $link = $this->generateUrl('bf_site_profile_duels');
    	//we create a notification for the guest.
        $service = $this->container->get('bf_site.notification');
        $notification = $service->create($guest, $message, $duel, $link);

        $request = $this->get('request');
        if($request->getLocale() == 'en'){
            $form = $this->get('form.factory')->create(new DuelType, $duel);
        }
        elseif($request->getLocale() == 'fr'){
            $form = $this->get('form.factory')->create(new DuelFRType, $duel);
        }

	    
	    
		    if ($form->handleRequest($request)->isValid()) {
                //we check if the user wants to receive a mail. If so, we send him an email.
                if($guest->getMailDuel() === true){
                    $message = \Swift_Message::newInstance()
                        ->setSubject($host->getUsername().' invited you for a duel on bestfootball')
                        ->setFrom('noreply@bestfootball.fr')
                        ->setTo($guest->getEmail())
                        ->setBody(
                            $this->renderView(
                                // app/Resources/views/Emails/registration.html.twig
                                'Emails/duelinvitation.html.twig',
                                array(
                                    'host' => $host,
                                    'guest' => $guest,
                                    'duel' => $duel
                                )
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                }

			    $em = $this->getDoctrine()->getManager();
			    $em->persist($notification);
			    $em->persist($duel);
			    $em->flush();

			    $this->addFlash('success', 'Your invitation for a duel has been send to '.$guest->getUsername().' you will have to wait for '.$guest->getUsername().' to accept it.');

			    return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $guest->getUsername())));
			}

	    return $this->render('BFSiteBundle:Duel:create.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }
    public function acceptAction(request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();

    	$guest = $this->container->get('security.context')->getToken()->getUser();
    	$duel = $em->getRepository('BFSiteBundle:Duel')->find($id);

    	if ($duel === null) {
	      throw $this->createNotFoundException("This duel doesn't exist");
	    }
    	
        $host = $duel->getHost();

        if($guest != $duel->getGuest()){
            //the user can't accept the duel
            throw new NotFoundHttpException("You can't accept this duel because it isn't yours.");
        }
   
    		//the user can accept.
    			$duel->setAccepted('1');

    			//we create the notification for the other user to say "accepted".
    			$message =$guest->getUsername().' accepted your invitation. You can now upload your video by clicking here or by going to your my duels page.';
    			//getting the other user
                $link = $this->generateUrl('bf_site_duel_view', array('id' => $duel->getId()));
                $service = $this->container->get('bf_site.notification');
                $notification = $service->create($host, $message, $duel,$link);
    			
    			$em->persist($duel);
			    $em->persist($notification);
			    $em->flush();

			    $this->addFlash('success', 'You accepted the duel from '.$host->getUsername());

			      return $this->redirect($this->generateUrl('bf_site_profile_duels'));
    }
    public function declineAction(request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $guest = $this->container->get('security.context')->getToken()->getUser();
        $duel = $em->getRepository('BFSiteBundle:Duel')->find($id);
        $host = $duel->getHost();

        if ($duel === null) {throw $this->createNotFoundException("This duel doesn't exist");}
        //on verifie que l'utilisateur peut decliner le duel
        if($guest != $duel->getGuest()){throw new NotFoundHttpException("You can't decline this duel because it isn't yours.");}
    	//we create the notification for the other user to say "accepted".
    	$message =$guest->getUsername().' declined your invitation.';
    	//getting the other user
    	//getting the notifications that are linked with the duel.
    	$notifications = $em->getRepository('BFSiteBundle:Notification')->findByDuel($duel);
		   		
		foreach ($notifications as $notification) { $em->remove($notification);}
    	$em->remove($duel);

        $duel = null;
        $link = $this->generateUrl('bf_site_profile_duels');
        $service = $this->container->get('bf_site.notification');
        $notification = $service->create($host, $message, $duel, $link);
		$em->persist($notification);
		$em->flush();

		$this->addFlash('success', 'You declined the duel from '.$host->getUsername());

		return $this->redirect($this->generateUrl('bf_site_profile_duels'));

    }
    public function myduelsAction(request $request)
    {
        //here we're going to make 3 lists of duels. Progress, won and lost
        $em = $this->getDoctrine()->getManager();
    	$user = $this->container->get('security.context')->getToken()->getUser();


        $listProgress = $em->getRepository('BFSiteBundle:Duel')->progressDuels($user);
        $listWon = $em->getRepository('BFSiteBundle:Duel')->wonDuels($user);
        $listLost = $em->getRepository('BFSiteBundle:Duel')->lostDuels($user);
        $listDraw = $em->getRepository('BFSiteBundle:Duel')->drawDuels($user);


    	$listDuels = array('listProgress' => $listProgress, 'listWon' => $listWon,'listDraw' => $listDraw, 'listLost' => $listLost);

        //the notifications. Will be done with AJAx in the future.
        

    	 return $this->render('BFSiteBundle:Profile:duels.html.twig', array(
		      'listDuels' => $listDuels,
		      'user' => $user,
		    ));
    }
}
