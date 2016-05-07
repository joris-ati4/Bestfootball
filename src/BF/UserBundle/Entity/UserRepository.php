<?php

namespace BF\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
	public function myLogin($username)
  {
    // Méthode 1 : en passant par l'EntityManager
    $queryBuilder = $this->_em->createQueryBuilder()
      ->select('u')
      ->from($this->_entityName, 'u')
    ;
    $query = $queryBuilder->getQuery();
    $results = $query->getResult();
    return $results;
  }
  public function globalRanking()
  {
    $qb = $this->createQueryBuilder('u');

    $qb->orderBy('u.points', 'DESC');
    
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function globalDuelRanking()
  {
    $qb = $this->createQueryBuilder('u');

    $qb->orderBy('u.duelPoints', 'DESC')
      ->setMaxResults(10);
    
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function globalDuelRankingGirls()
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.gender = :gender')
         ->setParameter('gender', 'Female')
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10);
    
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryRanking($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryDuelRanking($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateRanking($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateDuelRanking($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryRankingGirls($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Female')
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryDuelRankingGirls($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Female')
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateRankingGirls($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Female')
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateDuelRankingGirls($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Female')
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryRankingBoys($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Male')
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function countryDuelRankingBoys($country)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.country = :country')
         ->setParameter('country', $country)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Male')
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateRankingBoys($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Male')
       ->orderBy('u.points', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  public function stateDuelRankingBoys($state)
  {
    $qb = $this->createQueryBuilder('u');

    $qb->Where('u.state = :state')
         ->setParameter('state', $state)
       ->andWhere('u.gender = :gender')
         ->setParameter('gender', 'Male')
       ->orderBy('u.duelPoints', 'DESC')
       ->setMaxResults(10)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }

  public function findUserLike( $term, $limit = 10 )
  {
   
    $qb = $this->createQueryBuilder('u');
    $qb
    ->where('u.username LIKE :term')
      ->setParameter('term', '%'.$term.'%')
    ->setMaxResults($limit);
   
    $arrayAss= $qb->getQuery()
  ->getArrayResult();
 
  // Transformer le tableau associatif en un tableau standard
  $array = array();
  $i = 1;
  foreach($arrayAss as $data)
  {
    $array[] = array("id"=>$data['id'], "name"=>$data['username'], "value"=>$data['username']);
    $i++;
  }
 
  return $array;
  }

  public function getUsers($page, $nbPerPage)
  {
    $query = $this->createQueryBuilder('u')
      ->orderBy('u.username', 'ASC')
      ->getQuery()
    ;
    $query
      ->setFirstResult(($page-1) * $nbPerPage)
      ->setMaxResults($nbPerPage)
    ;
    // Enfin, on retourne l'objet Paginator correspondant à la requête construite
    // (n'oubliez pas le use correspondant en début de fichier)
    return new Paginator($query, true);
  }

}
