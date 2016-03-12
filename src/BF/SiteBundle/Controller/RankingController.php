<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RankingController extends Controller
{
    public function challengeAction($country,$state)
    {
        $listCountries = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country')->findall();
        if($country == 'global'){ //the global ranking of all the users
            $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array(),array('points' => 'desc'),10,0);
            $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('gender' => 'Female'),array('points' => 'desc'),10,0);
            $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('gender' => 'Male'),array('points' => 'desc'),10,0);
            }
        else{ 
            if($state == 'country'){//rankings for country
                $country = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country')->findOneByName($country);
                $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryRanking($country);
                $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryRankingGirls($country);
                $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryRankingBoys($country);}
            else{ //ranking for state
                $state = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:State')->findOneByName($state);
                $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateRanking($state);
                $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateRankingGirls($state);
                $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateRankingBoys($state);
            }
        }

        $rankings = array('rankingGirls' => $rankingGirls, 'rankingBoys' => $rankingBoys,);
        //rankings for state
            return $this->render('BFSiteBundle:Ranking:challenge.html.twig',array(
              'ranking' => $ranking,
              'rankings' => $rankings,
              'listCountries' => $listCountries,
              'country' => $country,
              'state' => $state,
            ));   
    }
    public function duelAction($country,$state)
    {
        $listCountries = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country')->findall();

        if($country == 'global'){ //the global ranking of all the users
            $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array(),array('duelPoints' => 'desc'));
            $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('gender' => 'Female'),array('duelPoints' => 'desc'));
            $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('gender' => 'Male'),array('duelPoints' => 'desc'));
        }
        else{ 
            if($state == 'country'){//rankings for country
                $country = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country')->findOneByName($country);
                $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryDuelRanking($country);
                $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryDuelRankingGirls($country);
                $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->countryDuelRankingBoys($country);
                
            }
            else{ //ranking for state
                $state = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:State')->findOneByName($state);
                $ranking = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateDuelRanking($state);
                $rankingGirls =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateDuelRankingGirls($state);
                $rankingBoys =$this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->stateDuelRankingBoys($state);
            }
        }
        //rankings for state
            $rankings = array('rankingGirls' => $rankingGirls, 'rankingBoys' => $rankingBoys,);
            return $this->render('BFSiteBundle:Ranking:duel.html.twig',array(
              'ranking' => $ranking,
              'rankings'=> $rankings,
              'listCountries' => $listCountries,
              'country' => $country,
              'state' => $state,
            ));   
    }
}
