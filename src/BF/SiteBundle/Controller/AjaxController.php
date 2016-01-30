<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    public function userInfoAction(request $request)
    {
        $username = $request->get('username');
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $user = $repository->findOneByUsername($username);

        $info = $this->container->get('bf_site.rankinfo');
        $rankinfo = $info->rankInfo($user);
        $duelRankInfo = $info->duelRankInfo($user);

        $rank = array('rankinfo' => $rankinfo, 'duelrankinfo' => $duelRankInfo);

        return $this->render('BFSiteBundle:Userinfo:userinfo.html.twig', array(
            'rank' => $rank,
            'user' => $user,
            ));
    }
    public function getChallengeAction(request $request)
    {
        $id = $request->get('id');
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);

        return $this->render('BFSiteBundle:Challenge:challengeAjax.html.twig', array(
            'challenge' => $challenge,
            ));
    }
    public function notifreadAction(request $request)
    {
        $em =$this->getDoctrine()->getManager();
        $id = $request->get('id');
        $notification = $em ->getRepository('BFSiteBundle:Notification')->find($id);

        $notification->setWatched('1');
        $em->persist($notification);
        $em->flush();

       return new response();
    }
 
}