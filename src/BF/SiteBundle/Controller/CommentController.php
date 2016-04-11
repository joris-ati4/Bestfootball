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
        $link = $this->generateUrl('bf_site_video', array('code' => $video->getCode()));
        $service = $this->container->get('bf_site.notification');
        $notification = $service->create($video->getUser(), $message, null, $link);

        $em->persist($comment);
        $em->persist($notification);
        $em->flush();

        if($video->getUser()->getMailComment() === true){
            $message = \Swift_Message::newInstance()
                ->setSubject($user->getUsername().' posted a new comment on your '.$video->getTitle().' video')
                ->setFrom('bestfootball@bestfootball.fr')
                ->setTo($video->getUser()->getEmail())
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'Emails/comment.html.twig',
                        array(
                            'user' => $video->getUser(),
                            'commenter' => $user,
                            'video' => $video
                        )
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
        }

        $this->addFlash('success', 'Your comment has been added.');

        return new response();
    }
    public function deleteAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $comment = $em->getRepository('BFSiteBundle:Comment')->find($request->get('commentId'));
        $video = $comment->getVideo();

        //check who deleted the comment.
        if($user->getId() != $comment->getUser()->getId()){
            //we send notification to the user of the video
            $message = $user->getUsername().' just deleted your comment on his '.$video->getTitle().' video.';
            $link = $this->generateUrl('bf_site_video', array('code' => $comment->getVideo()->getCode()));
            $service = $this->container->get('bf_site.notification');
            $notification = $service->create($comment->getVideo()->getUser(), $message, null, $link);
            $em->persist($notification);
        }
        
        $em->remove($comment);
        $em->flush();

        return new response();
    }
}
