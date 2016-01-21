<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Duel;
use BF\SiteBundle\Entity\Notification;
//les types
use BF\SiteBundle\Form\Type\DuelType;

class DuelController extends Controller
{
    public function viewAction($id,request $request)
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
	    $duel = $em->getRepository('BFSiteBundle:Duel')->find($id);
	    if (null === $duel) {
	      throw new NotFoundHttpException("This duel doesn't exist");
	    }

      	return $this->render('BFSiteBundle:Duel:view.html.twig', array(
	      	'duel'           => $duel,
            'search'         => $search->createView(),
	    	));
    }
    public function createAction(request $request, $username)
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
    	//we get the host and the invited user
    	$host = $this->container->get('security.context')->getToken()->getUser();

    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$guest = $repository->findOneByUsername($username);

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
    	$duel 
    		->setBeginDate(new \Datetime())
    		->setEndDate($date)
    		->setAccepted('0')
    		->setCompleted('0')
    		->setHostCompleted('0')
    		->setGuestCompleted('0')
    		->setHost($host->getUsername())
    		->setGuest($guest->getUsername())
            ->setType('duel')
    		->addUser($host)
    		->addUser($guest)
    		;

    	$message = 'You received an invitation for a duel from '.$host->getUsername();
    	//we create a notification for the guest.
    	$notification = new Notification();
    	$notification
    		->setDate(new \Datetime())
    		->setMessage($message)
    		->setUser($guest)
    		->setWatched('0')
    		->setDuel($duel)
   		;
	    
	    $form = $this->get('form.factory')->create(new DuelType, $duel);
	    
		    if ($form->handleRequest($request)->isValid()) {
			      $em = $this->getDoctrine()->getManager();
			      $em->persist($notification);
			      $em->persist($duel);
			      $em->flush();

			      $this->addFlash('success', 'Your invitation for a duel has been send to '.$guest->getUsername().' you will have to wait for '.$guest->getUsername().' to accept it.');

			      return $this->redirect($this->generateUrl('bf_site_profile', array('username' => $guest->getUsername())));
			    }

		    return $this->render('BFSiteBundle:Duel:create.html.twig', array(
		      'form' => $form->createView(),
              'search'  => $search->createView(),
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
    	$users = $duel->getUsers();

    	foreach ($users as $user) {
    		if($user != $guest){ $host = $user;}
    		elseif($user == $guest){ $guest = $user; $verified = 'ok';}
    		else{}
    	}

   
	      //on verifie que l'utilisateur peut accepter le duel
    		if($verified != 'ok'){ 
    			throw new NotFoundHttpException("You can't accept this duel because it isn't yours.");
    		}

    		//the user can accept.
    			$em = $this->getDoctrine()->getManager();
    			$duel->setAccepted('1');

    			//we create the notification for the other user to say "accepted".
    			$message =$guest->getUsername().' accepted your invitation. You can now upload your video by clicking here or by going to your my duels page.';
    			//getting the other user
    			
    			$notification = new Notification();
		    	$notification
		    		->setDate(new \Datetime())
		    		->setMessage($message)
		    		->setUser($host)
		    		->setWatched('0')
		    		->setDuel($duel)
		   		;
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
    	if ($duel === null) {
	      throw $this->createNotFoundException("This duel doesn't exist");
	    }
    	$users = $duel->getUsers();

    	foreach ($users as $user) {
    		if($user != $guest){ $host = $user;}
    		elseif($user == $guest){ $guest = $user; $verified = 'ok';}
    		else{}
    	}
	      //on verifie que l'utilisateur peut decliner le duel
    		if($verified == 'ok'){ //the user can accept.
    			$em = $this->getDoctrine()->getManager();
    			//we create the notification for the other user to say "accepted".
    			$message =$guest->getUsername().' declined your invitation.';
    			//getting the other user
    			//getting the notifications that are linked with the duel.
    			$notifications = $em->getRepository('BFSiteBundle:Notification')->findByDuel($duel);
		   		
		   		foreach ($notifications as $notification) {
		    		$em->remove($notification);
		    	}
    			$em->remove($duel);

    			$notification = new Notification();
		    	$notification
		    		->setDate(new \Datetime())
		    		->setMessage($message)
		    		->setUser($host)
		    		->setWatched('0')
		   		;

			    $em->persist($notification);
			    $em->flush();

			    $this->addFlash('success', 'You declined the duel from '.$host->getUsername());

			      return $this->redirect($this->generateUrl('bf_site_profile_duels'));
    		}
    		else{
    			throw new NotFoundHttpException("You can't decline this duel because it isn't yours.");
    		}   
    }
    public function myduelsAction(request $request)
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
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	$listDuels = $user->getDuels();
        $notifications = $user->getNotifications();

        foreach ($notifications as $notification) {
                    $notification->setWatched('1');
                    $em->persist($notification);
                }
        $em->flush();

    	 return $this->render('BFSiteBundle:Profile:duels.html.twig', array(
		      'listDuels' => $listDuels,
		      'user' => $user,
              'search'         => $search->createView(),
		    ));
    }
}
