<?php
namespace BF\SiteBundle\RandomCode;

use Doctrine\ORM\EntityManager;

class BFRandomCode
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

  public function generate($type)
  {
  	$length=10;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    }


    //we check if the string is already used for another element.
    if($type == 'duel'){
    	$check = $this->em->getRepository('BFSiteBundle:Duel')->checkCode($randomString);
    	while($check != null ){
    		for ($i = 0; $i < $length; $i++) {
        		$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    		}
    		$check = $this->em->getRepository('BFSiteBundle:Duel')->checkCode($randomString);
    	}
    }
    elseif($type == 'video'){
    	$check = $this->em->getRepository('BFSiteBundle:Video')->checkCode($randomString);
    	while($check != null ){
    		for ($i = 0; $i < $length; $i++) {
        		$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    		}
    		$check = $this->em->getRepository('BFSiteBundle:Video')->checkCode($randomString);
    	}
    }
  
    return $randomString;
  }
}
