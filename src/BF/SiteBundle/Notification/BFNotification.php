<?php
namespace BF\SiteBundle\Notification;

use BF\SiteBundle\Entity\Notification;

class BFNotification
{
   /**
   * Create Notifications
   *
   */

  public function create($user, $message, $duel, $link)
  {
      //we create a notification for the guest.
      $notification = new Notification();
      $notification
        ->setDate(new \Datetime())
        ->setMessage($message)
        ->setUser($user)
        ->setWatched('0')
        ->setLink($link)
      ;

      if($duel !== null){
        $notification->setDuel($duel);
      }

    return $notification;
  }
}
