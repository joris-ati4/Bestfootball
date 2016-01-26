<?php
namespace BF\SiteBundle\Notification;

use BF\SiteBundle\Entity\Notification;

class BFNotification
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
      ;

      if($duel != null){
        $notification->setDuel($duel);
      }

    return $notification;
  }
}
