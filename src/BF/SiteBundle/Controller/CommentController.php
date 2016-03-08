<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BF\SiteBundle\Entity\Comment;

class CommentController extends Controller
{
    public function createAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //getting the content
        $content = $request->get('content');
        //getting the user
        $user = $this->container->get('security.context')->getToken()->getUser();
        //getting the video
        $video = $em->getRepository('BFSiteBundle:Video')->find($request->get('videoId'));

        $comment = new Comment();
        $comment
            ->setContent($content)
            ->setVideo($video)
            ->setDate(new \Datetime)
            ->setUser($user)
        ;
        //we create a notification for the user of the video.
        $message = $user->getUsername().' just submitted a comment on your '.$video->getTitle().' video.';
        $link = $this->generateUrl('bf_site_video', array('id' => $video->getId()));
        $service = $this->container->get('bf_site.notification');
        $notification = $service->create($video->getUser(), $message, null, $link);

        $em->persist($comment);
        $em->persist($notification);
        $em->flush();

        $this->addFlash('success', 'Your comment has been added.');

        return new response();
    }
    public function deleteAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $comment = $em->getRepository('BFSiteBundle:Comment')->find($request->get('commentId'));

        //check who deleted the comment.
        if($user->getId() != $comment->getUser()->getId()){
            //we send notification to the user of the video
            $message = $comment->getUser()->getUsername().' just deleted his comment on your '.$video->getTitle().' video.';
            $link = $this->generateUrl('bf_site_video', array('id' => $comment->getVideo()->getId()));
            $service = $this->container->get('bf_site.notification');
            $notification = $service->create($comment->getVideo()->getUser(), $message, null, $link);
            $em->persist($notification);
        }

        $this->addFlash('success', 'This comment has been deleted.');
        
        $em->remove($comment);
        $em->flush();

        return new response();
    }
}
