<?php
// src/OC/PlatformBundle/Antispam/OCAntispam.php

namespace BF\SiteBundle\Userinfo;

use Doctrine\ORM\EntityManager;
use BF\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

class BFUserinfo
{
   /**
   * Retrieve all the information about a user.
   *
   */
  protected $doctrine;

  public function __construct(EntityManager $em, TranslatorInterface $translator)
  {
      $this->em = $em;
      $this->translator = $translator;
  }


  public function rankInfo(user $user)
  {
  	$points = $user->getPoints();
    if( '0'<= $points && $points <= '1000'){$level = $this->translator->trans('Unknown'); $percent=($points/1000)*100;$min=0;$max=1000;$style='progress-bar-success';} //incognito
    if( '1000'< $points && $points <= '2000'){$level = $this->translator->trans('Promising Talent');$percent=(($points-1000)/1000)*100;$min=1000;$max=2000;$style='progress-bar-success';}
    if( '2000'< $points && $points <= '3500'){$level = $this->translator->trans('Rising Star');$percent=(($points-2000)/1500)*100;$min=2000;$max=3500;$style='progress-bar-info';}
    if( '3500'< $points && $points <= '5999'){$level = $this->translator->trans('Real Star');$percent=(($points-3500)/1499)*100;$min=3500;$max=5999;$style='progress-bar-warning';}
    if( '6000'<= $points){$level = $this->translator->trans('Legend');$percent=(($points-6000)/2000)*100;$min=6000;$max=8000;$style='progress-bar-danger';}

    //now we are going to determine the place of the user.
 
    $repository = $this->em->getRepository('BFUserBundle:User');
    $globalRank = $repository->globalRanking();
    $countryRank = $repository->countryRankingFull($user->getCountry());
    $stateRank = $repository->stateRankingFull($user->getState());

    $globalRank = array_search($user, $globalRank) + 1;
    $countryRank = array_search($user, $countryRank) + 1;
    $stateRank = array_search($user, $stateRank) + 1;

    //making an object to return all these informations

    $rankInfo = array('level' => $level,'percent' => $percent,'min' => $min ,'max' => $max ,'style' => $style ,'globalrank' => $globalRank ,'countryrank' => $countryRank ,'staterank' => $stateRank);
    return $rankInfo;
  }
  public function duelRankInfo(user $user)
  {
    $points = $user->getDuelPoints();
    if( '0' == $points){$level = 0;} 
    if( '0'< $points && $points <= '1000'){$level = 1;} //incognito
    if( '1000'< $points && $points <= '2000'){$level = 2;}
    if( '2000'< $points && $points <= '3000'){$level = 3;}
    if( '3000'< $points){$level = 4;}
   

    //now we are going to determine the place of the user.

    $repository = $this->em->getRepository('BFUserBundle:User');
    $globalRank = $repository->globalDuelRanking();
    $countryRank = $repository->countryDuelRanking($user->getCountry());
    $stateRank = $repository->stateDuelRanking($user->getState());

    $globalRank = array_search($user, $globalRank) + 1;
    $countryRank = array_search($user, $countryRank) + 1;
    $stateRank = array_search($user, $stateRank) + 1;

    //making an object to return all these informations

    $rankInfo = array('level' => $level);
    return $rankInfo;
  }
}
