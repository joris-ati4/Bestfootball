<?php

namespace BF\SiteBundle\Entity;

/**
 * DuelRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DuelRepository extends \Doctrine\ORM\EntityRepository
{
	public function listDuelsComplete($user)
	{
	  $qb = $this->createQueryBuilder('d');

	  $qb->where('d.host = :user OR d.guest =:user' )
	       ->setParameter('user', $user->getUsername())
	     ->andWhere('d.completed = :completed')
	       ->setParameter('completed', '1')
	     ->orderBy('d.beginDate', 'DESC')
	  ;

	  return $qb
	    ->getQuery()
	    ->getResult()
	  ;
	}
}
