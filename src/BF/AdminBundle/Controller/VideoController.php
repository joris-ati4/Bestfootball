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
                //get all the videos of the user.
                $listVideos = $em->getRepository('BFSiteBundle:Video')->allVideos($user);

                //recount the points of the user
                $points = 0;
                $oldvideo = null;
                foreach ( $listVideos as $video) {
                  //compter les likes pour la vidéo.
                  $likePoints = count($video->getLikes()) * 5; // 5 points par like.
                  $points = $points + $likePoints;

                  //compter les points de la vidéo et éventuellement les 20 points d'entraînement
                  if($oldvideo === null){
                    $points = $points + $video->getScore();
                  }
                  elseif($oldvideo->getChallenge()->getId() == $video->getChallenge()->getId()){ //It's the same challenge.
                    //Look for 20 points
                    if($video->getScore() < $oldvideo->getScore()){
                      //give 20 points for improvement.
                      $points = $points + 20;
                    }
                  }
                  elseif($oldvideo->getChallenge()->getId() != $video->getChallenge()->getId()){ //It's a new challenge.
                   $points = $points + $video->getScore();
                  }

                  $oldvideo = $video;
                }
                
                $user->setPoints($points);
                $em->persist($user);

                $em->flush();
              }

          $request->getSession()->getFlashBag()->add('notice', 'The video has been deleted');

          return $this->redirect($this->generateUrl('bf_site_admin_user_view', array('id' => $video->getUser()->getId()) ));
        }
        return $this->render('BFAdminBundle:Video:del.html.twig',array(
            'form' => $form->createView(),
        ));
    }
    /* public function uploadYoutubeAction(request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $listVideos = $em->getRepository('BFSiteBundle:Video')->findBy(array('youtube' => 0), array("id" => "ASC"));


      #accesing the OAuthclient
      $OAUTH2_CLIENT_ID = '681480420896-do7git7anrsb663rqgut2ki8nm04qs05.apps.googleusercontent.com';
      $OAUTH2_CLIENT_SECRET = 'Ldn3lBrzX6Wz3U1-DuS_82LP';

      $client = new Google_Client();
      $client->setClientId($OAUTH2_CLIENT_ID);
      $client->setClientSecret($OAUTH2_CLIENT_SECRET);
      $client->setScopes('https://www.googleapis.com/auth/youtube');
      $redirect = 'http://bestfootball.fr/admin/youtube';
      $client->setRedirectUri($redirect);

      $youtube = new Google_Service_YouTube($client);
      
      if (null !== $request->query->get('code')) {
        
        $client->authenticate($request->query->get('code'));
        $_SESSION['token'] = $client->getAccessToken();
        header('Location: ' . $redirect);
      }

      if (null !== $_SESSION['token']) {
        $client->setAccessToken($_SESSION['token']);
      }

      if ($client->getAccessToken()) {

        foreach ($lisVideos as $vid) {
            $videoPath = $vid->getSource();

            $snippet = new Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle($vid->getTitleFR());
            $snippet->setDescription($vid->getDescritpionFR());
            $snippet->setTags(array("Bestfootball", "freestyle", "foot"));

            // Numeric video category. See
            // https://developers.google.com/youtube/v3/docs/videoCategories/list 
            $snippet->setCategoryId("17");

            // Set the video's status to "public". Valid statuses are "public",
            // "private" and "unlisted".
            $status = new Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = "public";

            // Associate the snippet and status objects with a new video resource.
            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            $client->setDefer(true);

            $req = $youtube->videos->insert('status,snippet', $video);

            $media = new Google_Http_MediaFileUpload($client, $req,'video/*', file_get_contents($videoPath));

            $video = $client->execute($request);

          #Putting the state of youtube to 1.
          $video->setYoutube(1);
          $em->persist($video);
          $em->flush();
          unset($vid);
        }
      } 
    } */
}
