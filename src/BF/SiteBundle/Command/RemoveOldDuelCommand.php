<?php 
// src/Acme/DemoBundle/Command/GreetCommand.php 
namespace BF\SiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOldDuelCommand extends ContainerAwareCommand 
{ 

	protected function configure() 
	{ 
		$this ->setName('Duel:Remove') 
		->setDescription('Delete the old not finished duels')
		; 
	} 

	protected function execute(InputInterface $input, OutputInterface $output) 
	{
		//retrieve all the not finished duels
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$date = new \Datetime();
	    $listDuels = $em->getRepository('BFSiteBundle:Duel')->notFinishedDuels($date);

	    foreach ($listDuels as $duel) {
	    	$notifications = $em->getRepository('BFSiteBundle:Notification')->findByDuel($duel);
	    	$videos = $em->getRepository('BFSiteBundle:Video')->findByDuel($duel);
	    	if($duel->getAccepted() == '0'){
	    		//not accepted. We just delete the notifications and the duel
	    		foreach ($notifications as $notification){ 
	    			$em->remove($notification);
	    		}
	    		$em->remove($duel);
	    		
	    	}
	    	else{
	    		//the duel is accepted. check if a user uploaded a video.
	    		if($duel->getHostCompleted() == '1'){
	    			//the host completed the challenge. Give him the points
	    			$user = $duel->getHost();
	    			$points = $user->getDuelPoints() + 100;
	    			$user->setDuelPoints($points);

	    			$link = $this->getContainer()->get('router')->generate('bf_site_profile_duels');
	    			$message = $duel->getGuest()->getUsername().' did not complete the duel in time, your receive 100 points';
			      	$service = $this->getContainer()->get('bf_site.notification');
                	$notification = $service->create($user, $message, null, $link);

                	
                	foreach ($notifications as $notification) { $em->remove($notification);}
                	foreach ($videos as $video) { $em->remove($video);}
                	$em->persist($user);
                	$em->persist($notification);
	    			$em->remove($duel);
	    			

	    		}
	    		elseif($duel->getGuestCompleted() == '1'){
	    			//the guest completed the challenge. Give him the points
	    			$user = $duel->getGuest();
	    			$points = $user->getDuelPoints() + 100;
	    			$user->setDuelPoints($points);

	    			$link = $this->getContainer()->get('router')->generate('bf_site_profile_duels');
	    			$message = $duel->getHost()->getUsername().' did not complete the duel in time, your receive 100 points';
			      	$service = $this->getContainer()->get('bf_site.notification');
                	$notification = $service->create($user, $message, null, $link);

                	
                	foreach ($notifications as $notification) { $em->remove($notification);}
                	foreach ($videos as $video) { $em->remove($video);}
	    			
	    			$em->persist($notification);
	    			$em->persist($user);
	    			$em->remove($duel);
	    		}
	    		else{
	    			//nobody completed the challenge.
	    			$guest = $duel->getGuest();
	    			$host = $duel->getHost();
	    			
	    			$link = $this->getContainer()->get('router')->generate('bf_site_profile_duels');
	    			$message = 'you did not complete the duel in time. The duel was deleted';
			      	$service = $this->getContainer()->get('bf_site.notification');
                	$notificationhost = $service->create($host, $message, null, $link);
                	$notificationguest = $service->create($guest, $message, null, $link);
                	
                	foreach ($notifications as $notification) { $em->remove($notification);}
                	foreach ($videos as $video) { $em->remove($video);}
	    			
	    			$em->persist($notificationhost);
	    			$em->persist($notificationguest);
	    			$em->remove($duel);
	    		}

	    	}
	    }
	    $em->flush();
	}
}
