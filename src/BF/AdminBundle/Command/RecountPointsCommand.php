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

      	foreach ( $listUsers as $user) {
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
		      
		    $user->setPoints($points);
		    $em->persist($user);
		  	$em->flush();

		  	$output->writeln("Les points de l'utilisateur:  ".$user->getUsername()." on été mis à jour, ".$user->getPoints()." points!");
		}
	}
}
