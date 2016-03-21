<?php
namespace BF\SiteBundle\VideosPoints;

use Doctrine\ORM\EntityManager;
use BF\SiteBundle\Entity\Video;

class BFVideosPoints
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
  	$highestVideo =  $this->em->getRepository('BFSiteBundle:Video')->highestVideo($user, $challenge);


  	if($video->getId() == $highestVideo->getId()){
  		//this is the highest scoring video. So we have to adjust the score of the user.


  		$points = $user->getPoints() - $video->getScore();

  		if($video->getRepetitions() >= $challenge->getSix()){$video->setScore('300');}
        if($challenge->getSix() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFive()){ $video->setScore('250');}
        if($challenge->getFive() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFour()){$video->setScore('200');}
        if($challenge->getFour() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getThree()){$video->setScore('150');}
        if($challenge->getThree() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getTwo()){$video->setScore('100');}
        if($challenge->getTwo() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getOne()){$video->setScore('50');}
        if($challenge->getOne() > $video->getRepetitions()){$video->setScore('0');}

  		//now we update the points of the user

		$newPoints = $video->getScore() + $points;
		$user->setPoints($newPoints);
		$this->em->persist($user);
		$this->em->persist($video);
		
  	}
  	else{

  		//This isn't the highest video so we don't have to adjust the score of the user.
  		if($video->getRepetitions() >= $challenge->getSix()){$video->setScore('300');}
        if($challenge->getSix() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFive()){ $video->setScore('250');}
        if($challenge->getFive() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFour()){$video->setScore('200');}
        if($challenge->getFour() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getThree()){$video->setScore('150');}
        if($challenge->getThree() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getTwo()){$video->setScore('100');}
        if($challenge->getTwo() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getOne()){$video->setScore('50');}
        if($challenge->getOne() > $video->getRepetitions()){$video->setScore('0');}

		$this->em->persist($video);
  	}

  	$this->em->flush();
  	$done = true;

    return $done;
  }
}
