<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BF\SiteBundle\Entity\Likes;

class LikeController extends Controller
{
    public function createAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository('BFSiteBundle:Video')->find($request->get('videoId'));

        //check if the person already liked the vidéo.
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
            $user = $this->container->get('security.context')->getToken()->getUser();
        }
        else{
            $user = null;
        }
    
        $like = new Likes();
        $like
            ->setVideo($video)
            ->setDate(new \Datetime)
            ->setIpAdress($this->container->get('request')->getClientIp())
            ->setUser($user);
        ;
        
                
        $userVideo = $video->getUser();
        $points = $userVideo->getPoints();
        $userVideo->setPoints($points + 5);
        $em->persist($userVideo);
        
        
        //we create a notification for the user of the video.
        $message = 'Tu as reçu un nouveau like sur ta vidéo.';
        $link = $this->generateUrl('bf_site_video', array('code' => $video->getCode()));
        $service = $this->container->get('bf_site.notification');
        $notification = $service->create($video->getUser(), $message, null, $link);

        $em->persist($like);
        $em->persist($notification);
        $em->flush();

        return new response();
    }
    public function deleteAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $video = $em->getRepository('BFSiteBundle:Video')->find($request->get('videoId'));
        
        
        //check if the person already liked the vidéo.
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $like = $em->getRepository('BFSiteBundle:Likes')->getLikeByUser($user, $video);
        }
        else{
            $user = null;
            $like = $em->getRepository('BFSiteBundle:Likes')->getLikeByIP($this->container->get('request')->getClientIp(), $video);
        }
        
        $points = $video->getUser()->getPoints();
        $video->getUser()->setPoints($points - 5);
        $em->persist($video->getUser());
        
        $em->remove($like);
        $em->flush();

        return new response();
    }
}
