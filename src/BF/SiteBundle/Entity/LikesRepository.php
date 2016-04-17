<?php

namespace BF\SiteBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * VideoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LikesRepository extends \Doctrine\ORM\EntityRepository
{
	public function getLike($user, $video)
	{
	  $qb = $this->createQueryBuilder('l');

	  $qb->where('l.user = :user')
	       ->setParameter('user', $user)
	     ->andWhere('l.video = :video')
	       ->setParameter('video', $video)
	     ->setMaxResults(1)
	  	;

	  return $qb
	    ->getQuery()
	    ->getOneOrNullResult()
	  ;
	}
}