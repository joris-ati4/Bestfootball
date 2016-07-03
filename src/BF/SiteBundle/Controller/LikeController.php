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
        //getting the user
        $user = $this->container->get('security.context')->getToken()->getUser();
        //getting the video
        $video = $em->getRepository('BFSiteBundle:Video')->find($request->get('videoId'));

        $like = new Likes();
        $like
            ->setVideo($video)
            ->setDate(new \Datetime)
            ->setUser($user)
        ;

        if($video->getType == 'challenge'){
            $points = $user->getPoints();
            $user->setPoints($points + 5);
            $em->persist($user);
        }
        
        //we create a notification for the user of the video.
        $message = $user->getUsername().' vient d\'aimer ta vidéo.';
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
        $like = $em->getRepository('BFSiteBundle:Likes')->getLike($user, $video);

        if($video->getType == 'challenge'){
            $points = $user->getPoints();
            $user->setPoints($points - 5);
            $em->persist($user);
        }

        
        $em->remove($like);
        $em->flush();

        return new response();
    }
}
