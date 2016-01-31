<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RankingController extends Controller
{
    public function challengeAction(request $request,$country,$state)
    {

        if($country == 'global'){ //the global ranking of all the users
            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $ranking = $repository->findBy(array(),array('points' => 'desc'));
            $rankingGirls =$repository->findBy(array('gender' => 'Female'),array('points' => 'desc'));
            $rankingBoys =$repository->findBy(array('gender' => 'Male'),array('points' => 'desc'));
            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
            $listCountries = $repository->findall();
        }
        else{ 
            if($state == 'country'){//rankings for country
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $country = $repository->findOneByName($country);
                $listCountries = $repository->findall();
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->countryRanking($country);
                $rankingGirls =$repository->countryRankingGirls($country);
                $rankingBoys =$repository->countryRankingBoys($country);

                
            }
            else{ //ranking for state
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:State');
                $state = $repository->findOneByName($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->stateRanking($state);
                $rankingGirls =$repository->stateRankingGirls($state);
                $rankingBoys =$repository->stateRankingBoys($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $listCountries = $repository->findall();
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
    public function duelAction(request $request,$country,$state)
    {

        if($country == 'global'){ //the global ranking of all the users
            $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
            $ranking = $repository->findBy(array(),array('duelPoints' => 'desc'));
            $rankingGirls =$repository->findBy(array('gender' => 'Female'),array('duelPoints' => 'desc'));
            $rankingBoys =$repository->findBy(array('gender' => 'Male'),array('duelPoints' => 'desc'));
            $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
            $listCountries = $repository->findall();
        }
        else{ 
            if($state == 'country'){//rankings for country
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $country = $repository->findOneByName($country);
                $listCountries = $repository->findall();
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->countryDuelRanking($country);
                $rankingGirls =$repository->countryDuelRankingGirls($country);
                $rankingBoys =$repository->countryDuelRankingBoys($country);
                
            }
            else{ //ranking for state
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:State');
                $state = $repository->findOneByName($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
                $ranking = $repository->stateDuelRanking($state);
                $rankingGirls =$repository->stateDuelRankingGirls($state);
                $rankingBoys =$repository->stateDuelRankingBoys($state);
                $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Country');
                $listCountries = $repository->findall();
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
