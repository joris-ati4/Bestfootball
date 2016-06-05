<?php

namespace BF\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//Les entités
use BF\SiteBundle\Entity\Challenge;
//les types
use BF\SiteBundle\Form\Type\ChallengeType;
use BF\SiteBundle\Form\Type\ChallengeEditType;
use BF\SiteBundle\Form\Type\ChallengeDelType;

class AdminController extends Controller
{
    public function addChallengeAction(request $request)
    {
	    // On crée un objet Challenge
	    $challenge = new Challenge();
        $challenge->setType('normal');

	    $form = $this->get('form.factory')->create(new ChallengeType, $challenge);
	    
	    if ($form->handleRequest($request)->isValid()) {            
	      $em = $this->getDoctrine()->getManager();
	      $em->persist($challenge);
	      $em->flush();

	      $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

	      return $this->redirect($this->generateUrl('bf_site_admin_challenges'));
	    }

	    return $this->render('BFAdminBundle:Admin:addChallenge.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }
    public function indexAction()
    {
        //display statistics about users, videos and challenges.

        //informations about the videos
        $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->findall();
            //getting the total views
            $totalViews = 0;
            foreach ($listVideos as $video){
                $totalViews += $video->getViews();
            }
        $totalVideos = count($this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->findall());
        $videosWeek = count($this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->videoWeek(new \Datetime("- 7 days")));

        //videos by différent users
        $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->findBy(array(),array('user' => 'desc'));
        $videoByDiferentUser = 0;
        $oldUser = null;
            foreach ($listVideos as $video){
                if($video->getUser()->getId() == $oldUser->getId()){
                    //add 1 to $videoByDiferentUser
                    $videoByDiferentUser = $videoByDiferentUser + 1;
                    $oldUser = $video->getUser();
                }
            }

        $videoInfo = array('totalVideos' => $totalVideos, 'videosWeek' => $videosWeek, 'videosTotalViews' => $totalViews, 'videosDiferentUser' => $videosDiferentUser);
        //informations about users.
        $totalUsers = count($this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findall());
        $numberDayUsers = count($this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->usersDay(new \Datetime("- 1 day")));
        $numberWeekUsers = count($this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->usersWeek(new \Datetime("- 7 days")));

        $userInfo = array('totalUsers' => $totalUsers, 'dayUsers' => $numberDayUsers, 'weekUsers' => $numberWeekUsers);


	    return $this->render('BFAdminBundle:Admin:index.html.twig',array(
            'videoInfo' => $videoInfo,
            'userInfo' => $userInfo,
        ));
    }
    public function usersAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$listUsers = $repository->findall();
	    return $this->render('BFAdminBundle:Admin:users.html.twig',array(
	    	'listUsers' => $listUsers,
	    	));
    }
    public function challengesAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
    	$listChallenges = $repository->findall();
	    return $this->render('BFAdminBundle:Admin:challenges.html.twig',array(
	    	'listChallenges' => $listChallenges,
	    	));
    }
    public function modChallengeAction(request $request, $id)
    {
        // On crée un objet Challenge
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);;

        $form = $this->get('form.factory')->create(new ChallengeEditType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

          return $this->redirect($this->generateUrl('bf_site_admin_challenges'));
        }

        return $this->render('BFAdminBundle:Admin:addChallenge.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    public function delChallengeAction(request $request, $id)
    {
        // On crée un objet Challenge
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);;

        $form = $this->get('form.factory')->create(new ChallengeDelType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();

        $listVideos = $challenge->getVideos();
        foreach ($listVideos as $video) {
            $video
                ->setChallenge(NULL)
                ->setType('freestyle')
            ;
            $em->persist($video);
        }


          $em->remove($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'the challenge was deleted');

          return $this->redirect($this->generateUrl('bf_site_admin_challenges'));
        }

        return $this->render('BFAdminBundle:Admin:delChallenge.html.twig', array(
          'form' => $form->createView(),
          'challenge' => $challenge,
        ));
    }
    public function reportsAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Report');
    	$listReports = $repository->findBy(array('treated' => 0));
	    return $this->render('BFAdminBundle:Admin:reports.html.twig',array(
	    	'listReports' => $listReports,
	    	));
    }
    public function deleteVideoReportAction($id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$report = $em->getRepository('BFSiteBundle:Report')->find($id);
    	$video = $report->getVideo();

    	//send a mail to all the reports for this video and mark every report as treated.
    	$listReports = $video->getReports();
    	foreach ($listReports as $report) {
    		$report->setTreated(1);
    		$user = $report->getUser();
    		$email = $report->getUser()->getEmail();

		    $message = \Swift_Message::newInstance()
		        ->setSubject('Thank you for your report '.$user->getUsername())
		        ->setFrom('noreply@bestfootball.fr')
		        ->setTo($email)
		        ->setBody(
		            $this->renderView(
		                // app/Resources/views/Emails/registration.html.twig
		                'Emails/thankreport.html.twig',
		                array('user' => $user)
		            ),
		            'text/html'
		        )
		    ;
		    $this->get('mailer')->send($message);
			$em->remove($report);
    	}

        $listComments = $video->getComments();
        foreach ($listComments as $comment) {
            $em->remove($comment);
        }
    	//we delete the video from our servers and from the user we send a mail + delete his points.
    	$user= $video->getUser();
    	$email = $user->getEmail();
    	$points = $user->getPoints() - $video->getScore();
    	$user->setPoints($points);

    	$message = \Swift_Message::newInstance()
			        ->setSubject($user->getUsername().', your video '.$video->getTitle().' was deleted from our servers.')
			        ->setFrom('noreply@bestfootball.fr')
			        ->setTo($email)
			        ->setBody(
			            $this->renderView(
			                // app/Resources/views/Emails/registration.html.twig
			                'Emails/deletedvideo.html.twig',
			                array('user' => $user,'video' => $video)
			            ),
			            'text/html'
			        )
			    ;
			    $this->get('mailer')->send($message);

    	//delete the video from our servers
    	unlink('/var/www/bestfootball.fr/shared/web/uploads/videos/{{ video.source }}');
    	unlink('/var/www/bestfootball.fr/shared/web/uploads/videos/thumbnail/{{ video.id }}.jpg');
    	$em->remove($video);
    	$em->persist($user);
        $em->flush();
        return $this->redirect($this->generateUrl('bf_site_admin_reports'));
    }
    public function okVideoAction($id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Report');
    	$report = $repository->findOneById($id);
    	$video = $report->getVideo();

    	$listReports = $video->getReports();
    	foreach ($listReports as $report) {
    		$report->setTreated(1);
			$em->persist($report);
    	}
    	$em->flush();
    	return $this->redirect($this->generateUrl('bf_site_admin_reports'));
    }
    public function getPredictionAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $challenge = $em->getRepository('BFSiteBundle:Challenge')->findOneBySlug($slug);
        //now we get the winner of the challenge.
        $listVideos = $em->getRepository('BFSiteBundle:Video')->findByChallenge(array('challenge' => $challenge),array('repetitions' => 'DESC'),3,0);
        $numberVideos = count($listVideos);

        //if there less then 3 videos
        if($numberVideos == 0){
            $challengeWinner = null;
            $challengeSecond = null;
            $challengeThird = null;
        }
        elseif($numberVideos == 1){
            $challengeWinner = $listVideos[0]->getUser();
            $challengeSecond = null;
            $challengeThird = null;
        }
        elseif($numberVideos == 2){
            $challengeWinner = $listVideos[0]->getUser();
            $challengeSecond = $listVideos[1]->getUser();
            $challengeThird = null;
        }
        else{
            $challengeWinner = $listVideos[0]->getUser();
            $challengeSecond = $listVideos[1]->getUser();
            $challengeThird = $listVideos[2]->getUser();
        }

        //now we get all the users that predicted this user for this challenge.
        $listPredictors = $em->getRepository('BFSiteBundle:Prediction')->listPredictions($challengeWinner, $challenge);
        $numberPredictors = count($listPredictors);
        if($listPredictors != 0){
            $index = array_rand($listPredictors, 1);
            $winner = $listPredictors[$index]->getVoter();
        }
        else{
            $winner = null;
        }

        return $this->render('BFAdminBundle:Admin:prediction.html.twig', array(
          'winner' => $winner,
          'challengeWinner' => $challengeWinner,
          'challengeSecond' => $challengeSecond,
          'challengeThird' => $challengeThird,

        ));
    }
    public function viewChallengeAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $challenge = $em->getRepository('BFSiteBundle:Challenge')->findOneBySlug($slug);

        $listVideos = $challenge->getVideos();
        return $this->render('BFAdminBundle:Admin:viewChallenge.html.twig', array(
          'listVideos' => $listVideos,
          'challenge' => $challenge,
        ));
    }
    public function deleteVideoAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository('BFSiteBundle:Video')->find($id);

        //send a mail to all the reports for this video and remove every report.
        $listReports = $video->getReports();
        foreach ($listReports as $report) {
            $report->setTreated(1);
            $user = $report->getUser();
            $email = $report->getUser()->getEmail();

            $message = \Swift_Message::newInstance()
                ->setSubject('Thank you for your report '.$user->getUsername())
                ->setFrom('noreply@bestfootball.fr')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'Emails/thankreport.html.twig',
                        array('user' => $user)
                    ),
                    'text/html'
                )
            ;
            $this->get('mailer')->send($message);
            $em->remove($report);
        }

        $listComments = $video->getComments();
        foreach ($listComments as $comment) {
            $em->remove($comment);
        }
        //we delete the video from our servers and from the user we send a mail + delete his points.
        $user= $video->getUser();
        $email = $user->getEmail();
        $points = $user->getPoints() - $video->getScore();
        $user->setPoints($points);

        $message = \Swift_Message::newInstance()
            ->setSubject($user->getUsername().', your video '.$video->getTitle().' was deleted from our servers.')
            ->setFrom('noreply@bestfootball.fr')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                    'Emails/deletedvideo.html.twig',
                    array('user' => $user,'video' => $video)
                ),
                'text/html'
            )
        ;
        $this->get('mailer')->send($message);

        //delete the video from our servers
        unlink('{{ video.source }}');
        unlink('{{ video.thumbUrl }}');
        $em->remove($video);
        $em->persist($user);
        $em->flush();
        return $this->redirect($this->generateUrl('bf_site_admin_reports'));
    }
}
