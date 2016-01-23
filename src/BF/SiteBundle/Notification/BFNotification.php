<?php
namespace BF\SiteBundle\Notification;

use Doctrine\ORM\EntityManager;
use BF\SiteBundle\Entity\Notification;

class BFUserinfo
{
   /**
   * Create Notifications
   *
   */

  public function create($user, $message, $duel)
  {
      //we create a notification for the guest.
      $notification = new Notification();
      $notification
        ->setDate(new \Datetime())
        ->setMessage($message)
        ->setUser($user)
        ->setWatched('0')
        ->setDuel($duel)
      ;
    return $notification;
  }
}
