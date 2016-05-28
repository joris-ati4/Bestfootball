<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ChallengeController extends Controller
{
    public function bannerAction()
    {
        //we retrieve the sponsorised challenges
        $listChallenges = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->findByPartner(1);

        //select a random challenge.
        if(!$listChallenges){
            //no sponsored challenges. We promote a random challenge
            $listChallenges = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->findall();
        }

        $index = array_rand($listChallenges, 1);
        $challenge = $listChallenges[$index];
        

        return $this->render('BFSiteBundle:Challenge:banner.html.twig', array(
          'challenge' => $challenge
        ));
    }
}