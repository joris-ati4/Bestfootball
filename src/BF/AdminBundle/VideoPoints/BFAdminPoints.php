<?php
namespace BF\AdminBundle\VideoPoints;

use Doctrine\ORM\EntityManager;
use BF\SiteBundle\Entity\Video;

class BFAdminPoints
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

  public function videoPoints(video $video)
  {
  	//we get the user
  	$user = $video->getUser();
  	$challenge = $video->getChallenge();
  	
  		if($video->getRepetitions() >= $challenge->getSix()){$video->setScore('300');}
      if($challenge->getSix() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFive()){ $video->setScore('250');}
      if($challenge->getFive() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFour()){$video->setScore('200');}
      if($challenge->getFour() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getThree()){$video->setScore('150');}
      if($challenge->getThree() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getTwo()){$video->setScore('100');}
      if($challenge->getTwo() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getOne()){$video->setScore('50');}
      if($challenge->getOne() > $video->getRepetitions()){$video->setScore('0');}

    //we flush the video.
      $this->em->persist($video);
      $this->em->flush();


      //recount the points of the user
      $listChallenges = $this->em->getRepository('BFSiteBundle:Challenge')->findall();
      $points = 0;
      foreach ( $listChallenges as $challenge) {
        $highestVideo =  $this->em->getRepository('BFSiteBundle:Video')->highestVideo($user, $challenge);
        if($highestVideo !== null){
          $points = $points + $highestVideo->getScore();
          $highestVideo = null; //Reset the value of the variable
        }
        
      }
      
    $user->setPoints($points);
    $this->em->persist($user);

  	$this->em->flush();
  	$done = true;

    return $done;
  }
}
