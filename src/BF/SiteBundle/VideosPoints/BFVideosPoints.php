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


    //if the video is for an ambassador challenge
    if($challenge->getType() != 'normal'){
      //we give the user 0 points
      $video->setScore('0');
    }
    else{
      if($video->getRepetitions() >= $challenge->getSix()){$video->setScore('300');}
      if($challenge->getSix() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFive()){ $video->setScore('250');}
      if($challenge->getFive() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getFour()){$video->setScore('200');}
      if($challenge->getFour() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getThree()){$video->setScore('150');}
      if($challenge->getThree() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getTwo()){$video->setScore('100');}
      if($challenge->getTwo() > $video->getRepetitions() && $video->getRepetitions() >= $challenge->getOne()){$video->setScore('50');}
      if($challenge->getOne() > $video->getRepetitions()){$video->setScore('0');}
    }

    //we flush the video.
      $this->em->persist($video);
      $this->em->flush();

      //get all the videos of the user.
      $listVideos = $this->em->getRepository('BFSiteBundle:Video')->allVideos($user);

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
    $this->em->persist($user);

    $this->em->flush();
    $done = true;

    return $done;
  }
}
