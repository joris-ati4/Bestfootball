<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BF\SiteBundle\Entity\Comment;
use BF\SiteBundle\Entity\Quote;

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
        $message = $user->getUsername().' a laissé un commentaire sur la vidéo: '.$video->getTitle().'.';
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
    public function addQuoteAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //getting the content
        $content = $request->get('content');
        //getting the user
        $user = $this->container->get('security.context')->getToken()->getUser();
        //getting the video
        $comment = $em->getRepository('BFSiteBundle:Comment')->find($request->get('commentId'));

        $quote = new Quote();
        $quote
            ->setContent($content)
            ->setComment($comment)
            ->setDate(new \Datetime)
            ->setUser($user)
        ;

        $video = $comment->getVideo();

        //We create a notification for the Video User + Comment User + Other quoters

            $link = $this->generateUrl('bf_site_video', array('code' => $video->getCode()));
            $service = $this->container->get('bf_site.notification');

            //we create a notification for the user of the video.
            if($user->getId() != $video->getUser()->getId()){
                $message1 = $user->getUsername().' a laissé un commentaire sur la vidéo: '.$video->getTitle().'.';
                $notification1 = $service->create($video->getUser(), $message1, null, $link);
                $em->persist($notification1);
            }
            

            //we create a notification for the user of the comment.
            if($user->getId() != $comment->getUser()->getId()){
                $message2 = $user->getUsername().' a répondu sur votre commentaire de la vidéo: '.$video->getTitle().'.';
                $notification2 = $service->create($comment->getUser(), $message2, null, $link);
                $em->persist($notification2);
            }


            //we create a notification for the user of the other Quotes.
            $listQuotes = $comment->getQuotes();
            if($listQuotes !== null){
                foreach($listQuotes as $quoter){
                    if($user->getId() != $quoter->getUser()->getId()){
                        $message3 = $user->getUsername().' a répondu sur un commentaire sur laquelle vous avez répondu.';
                        $notification3 = $service->create($quoter->getUser(), $message3, null, $link);
                        $em->persist($notification3);
                    }

                }
            }

            $em->persist($quote);
            
            
            $em->flush();

        $this->addFlash('success', 'Your comment has been added.');

        return new response();
    }
    public function delQuoteAction(request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $quote = $em->getRepository('BFSiteBundle:Quote')->find($request->get('quoteId'));
        
        $em->remove($quote);
        $em->flush();

        return new response();
    }

}
