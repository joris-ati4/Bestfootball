<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        return $this->render('BFSiteBundle:Ajax:userinfo.html.twig', array(
            'rank' => $rank,
            'user' => $user,
            ));
    }
 
}