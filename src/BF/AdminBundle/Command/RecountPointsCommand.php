<?php 
// src/Acme/DemoBundle/Command/GreetCommand.php 
namespace BF\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecountPointsCommand extends ContainerAwareCommand 
{ 

	protected function configure() 
	{ 
		$this ->setName('Recount:Points') 
		->setDescription('recompter les points de tous les utilisateurs.')
		; 
	} 

	protected function execute(InputInterface $input, OutputInterface $output) 
	{
		//retrieve all the not finished duels
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
	    $listUsers = $em->getRepository('BFUserBundle:User')->findBy(array("enabled" => 1));

	    //recount the points of the user
      	$listChallenges = $em->getRepository('BFSiteBundle:Challenge')->findall();
      	
      	foreach ( $listUsers as $user) {
      		$points = 0;
		    foreach ( $listChallenges as $challenge) {
		        $highestVideo =  $em->getRepository('BFSiteBundle:Video')->highestVideo($user, $challenge);
		        if($highestVideo !== null){
		          $points = $points + $highestVideo->getScore();
		          $highestVideo = null; //Reset the value of the variable
		        }
		        
		    }

		    $user->setPoints($points);
		    $em->persist($user);
		  	$em->flush();

		  	$output->writeln("Les points de l'utilisateur:  ".$user->getUsername()." on été mis à jour, ".$user->getPoints()." points!");
		}
	}
}
