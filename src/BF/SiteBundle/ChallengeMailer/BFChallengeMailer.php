<?php
namespace BF\SiteBundle\ChallengeMailer;

use Doctrine\ORM\EntityManager;
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Challenge;

class BFChallengeMailer
{
  /**
   * Return a list of semi-random videos
   *
   */
  protected $doctrine;

  public function __construct(EntityManager $em)
  {
      $this->em = $em;
      $this->mailer = $mailer;
      $this->templating = $templating;
  }

  public function ChallengeMail(video $newVideo, challenge $challenge)
  {
  	//Get all the videos that are under the current video repetitions
    $listVideos = $this->em->getRepository('BFSiteBundle:Video')->lowerRepetitionsVideos($challenge, $newVideo->getRepetitions());
    if($listVideos !== null){
      $subject = "Nouvelle vidéo pour le challenge ".$challenge->getTitleFR()."!";
      foreach ($listVideos as $video) {
        if($newVideo->getUser()->getId() != $video->getUser()->getId()){
          //send a mail to the user to notice him the new video.
          $message = \Swift_Message::newInstance()
              ->setSubject($subject)
              ->setFrom('noreply@bestfootball.fr')
              ->setTo($video->getUser()->getEmailCanonical())
              ->setBody(
                $this->render(
                  // app/Resources/views/Emails/newsletter.html.twig
                      'Emails/challenge/ScorePassed.html.twig',array(
                        'subject' => $subject,
                        'user' => $video->getUser(),
                        'opponentVideo' =>$newVideo,
                      )
                ),
                'text/html'
          );
          $this->mailer->send($message); //using the spool mailing method
          unset($message);
        }
      }
    }

    $done = true;

    return $done;
  }
}
