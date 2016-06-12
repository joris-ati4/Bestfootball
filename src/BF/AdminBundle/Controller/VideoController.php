<?php

namespace BF\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//Les entités
use BF\SiteBundle\Entity\Video;
//les types
use BF\SiteBundle\Form\Type\VideoModType;
use BF\SiteBundle\Form\Type\VideoDeleteType;

class VideoController extends Controller
{
    public function videosAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
    	$listVideos = $repository->findall();
	    return $this->render('BFAdminBundle:Video:index.html.twig',array(
	    	'listVideos' => $listVideos,
	    	));
    }
    public function modAction(request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $video = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->find($id);

        $form = $this->get('form.factory')->create(new VideoModType, $video);

        if ($form->handleRequest($request)->isValid()) {
          
          if($video->getType() == 'challenge'){
            //calculate the score of the video
            $service = $this->container->get('bf_admin.videopoints');
            $service->videoPoints($video);
          }

          $request->getSession()->getFlashBag()->add('notice', 'The video has been modified');

          return $this->redirect($this->generateUrl('bf_site_admin_user_view', array('id' => $video->getUser()->getId()) ));
        }

        return $this->render('BFAdminBundle:Video:mod.html.twig',array(
            'video' => $video,
            'form' => $form->createView(),
        ));
    }
    public function delAction(request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $video = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->find($id);
        $user = $video->getUser();
        $form = $this->get('form.factory')->create(new VideoDeleteType, $video);

        if ($form->handleRequest($request)->isValid()) {
              $oldVideo = $video;


              //deleting reports
              $listReports = $video->getReports();
              foreach ($listReports as $report) {
                $em->remove($comment);
              }
              //deleting comments and quotes
              $listComments = $video->getComments();
              foreach ($listComments as $comment) {
                    $listQuotes = $comment->getQuotes();
                    foreach ($listQuotes as $quote) {
                      $em->remove($quote);
                    }
                  $em->remove($comment);
              }

              //deleting likes
              $listLikes = $video->getLikes();
              foreach ($listLikes as $lik) {
                  $em->remove($lik);
              }
              $em->remove($video);
              $em->flush();

              if($oldVideo->getType() == 'challenge'){ //we need to update the users score
                $listChallenges = $em->getRepository('BFSiteBundle:Challenge')->findall();
                $points = 0;

                foreach ( $listChallenges as $challenge) {
                  $highestVideo =  $em->getRepository('BFSiteBundle:Video')->highestVideo($user, $challenge);
                  if( $highestVideo !== null){
                    $points = $points + $highestVideo->getScore();
                  }
                }
              }

              $user->setPoints($points);
              $em->persist($user);
            
              $em->flush();
          

          $request->getSession()->getFlashBag()->add('notice', 'The video has been deleted');

          return $this->redirect($this->generateUrl('bf_site_admin_user_view', array('id' => $video->getUser()->getId()) ));
        }
        return $this->render('BFAdminBundle:Video:del.html.twig',array(
            'form' => $form->createView(),
        ));

    }
}
