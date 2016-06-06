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
  }

  public function videoPoints(video $newVideo, challenge $challenge)
  {
  	//Get all the videos that are under the current video repetitions
    $listVideos = $this->em->getRepository('BFSiteBundle:Video')->lowerRepetitionsVideos($challenge, $newVideo->getRepetitions());
    if(!$listVideos){
      $subject = "Nouvelle vidéo pour le challenge ".$challenge->getTitleFR()."!";
      foreach ($listVideos as $video) {
        //send a mail to the user to notice him the new video.
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('noreply@bestfootball.fr')
            ->setTo($user->getEmailCanonical())
            ->setBody(
              $this->renderView(
                // app/Resources/views/Emails/newsletter.html.twig
                    'Emails/challenge/ScorePassed.html.twig',array(
                      'subject' => $subject,
                      'user' => $video->getUser(),
                      'opponentVideo' =>$newVideo,
                    )
              ),
              'text/html'
        );
        $this->get('swiftmailer.mailer.spool')->send($message); //using the spool mailing method
        unset($message);
      }
    }

    $done = true;

    return $done;
  }
}
