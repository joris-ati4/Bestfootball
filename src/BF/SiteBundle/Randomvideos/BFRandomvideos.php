<?php
namespace BF\SiteBundle\Randomvideos;

use Doctrine\ORM\EntityManager;
use BF\SiteBundle\Entity\Video;

class BFRandomvideos
{
    /**
   * Return a list of semi-random videos
   *
   */
  protected $doctrine;

  public function __construct(EntityManager $em)
  {
      $this->em = $em;
  }

  public function randomize(video $video)
  {
    //selecting the videos for the videolist.
	$listVideos = array();

	$videos = $this->em->getRepository('BFSiteBundle:Video')->listVideos($video->getUser()); //4 videos of the current user.
	$numberVideos = count($videos);
	if($numberVideos > 4){
	    $i = array_rand($videos, 4);
	        for($j = 0; $j < 4; $j++){
	            $index = $i[$j];
	            $object = $videos[$index];
	           	array_push($listVideos, $object);
	        }
	}
	else{
	    $k = $numberVideos-1;
	    for($j = 0; $j < $k; $j++){
	        $object = $videos[$j];
	        array_push($listVideos, $object);
	    }
	}
	 	
    if($video->getType() == 'challenge'){
       	//random 5 videos of the same challenge
	    $videosChallenge = $this->em->getRepository('BFSiteBundle:Video')->findByChallenge($video->getChallenge());
	    //verify that there are 5 videos.
	    $numberVideos = count($videosChallenge);
	    if($numberVideos > 5){
	        $i = array_rand($videosChallenge, 5);
	            for($j = 0; $j < 5; $j++){
	                $index = $i[$j];
	                $object = $videosChallenge[$index];
	            	array_push($listVideos, $object);
	            }
	        }
	        else{
	        	$k = $numberVideos-1;
	        	for($j = 0; $j < $k; $j++){
	                $object = $videosChallenge[$j];
	            	array_push($listVideos, $object);
	            }
	            //push the videos to the listVideos array
	        }
	        //random 6 videos
		 	$videos = $this->em->getRepository('BFSiteBundle:Video')->randomVideos();
		    $i = array_rand($videos, 6);
	        for($j = 0; $j < 6; $j++){
	            $index = $i[$j];
	            $object = $videos[$index];
	            array_push($listVideos, $object);
	        }
        }
    else{ //select 11 random videos.
		$videos = $this->em->getRepository('BFSiteBundle:Video')->randomVideos();
		$numberVideos = count($videos);
	        if($numberVideos > 11){
	            $i = array_rand($videos, 11);
	            for($j = 0; $j < 5; $j++){
	                $index = $i[$j];
	                $object = $videos[$index];
	            	array_push($listVideos, $object);
	            }
	        }
	        else{//push all the videos to the array
				$k = $numberVideos-1;
	        	for($j = 0; $j < $k; $j++){
	                $object = $videosChallenge[$j];
	            	array_push($listVideos, $object);
	            }
	        }
        }
    return $listVideos;
  }
}
