<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Notification;
use BF\SiteBundle\Entity\Follow;

class FollowController extends Controller
{
    public function followUserAction(request $request)
    {  
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        //we get the follower and the following
        $follower = $this->container->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $following = $repository->find($id);
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
        $follow = $repository->checkFollow($follower, $following);
        if($follow !== null ){//the user isn't following this user. we create a reponse.
            throw new NotFoundHttpException("You are already following this user.");}
        $follow = new Follow();
        $follow->setDate(new \Datetime())->setFollower($follower)->setFollowing($following);
        //send a notification to the following
            //if the user already had a notification like this we do not send another one.
        $message = $follower->getUsername().' is now following you!';
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Notification');
        $checkNotification = $repository->checkNotification($following, $message);
        if($checkNotification === null ){
            $service = $this->container->get('bf_site.notification');
            $notification = $service->create($following, $message, null);
            $em->persist($notification);}
        $em->persist($follow)->flush();
        return new Response();
    }
    public function unfollowUserAction(request $request)
    {
        $id = $request->get('id');

        $follower = $this->container->get('security.context')->getToken()->getUser();

        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $following = $repository->find($id);

        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Follow');
        $follow = $repository->checkFollow($follower, $following);

        if($follow === null ){//the user isn't following this user. we create a reponse.
            throw new NotFoundHttpException("You can't unfollow somebody that you aren't following.");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($follow);
        $em->flush();

        return new Response();

    }
    
}
