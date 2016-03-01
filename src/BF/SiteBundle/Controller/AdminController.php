<?php

namespace BF\SiteBundle\Controller;

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

	    return $this->render('BFSiteBundle:Admin:addChallenge.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }
    public function indexAction()
    {
	    return $this->render('BFSiteBundle:Admin:index.html.twig');
    }
    public function usersAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
    	$listUsers = $repository->findall();
	    return $this->render('BFSiteBundle:Admin:users.html.twig',array(
	    	'listUsers' => $listUsers,
	    	));
    }
    public function challengesAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
    	$listChallenges = $repository->findall();
	    return $this->render('BFSiteBundle:Admin:challenges.html.twig',array(
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

        return $this->render('BFSiteBundle:Admin:addChallenge.html.twig', array(
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

        return $this->render('BFSiteBundle:Admin:delChallenge.html.twig', array(
          'form' => $form->createView(),
          'challenge' => $challenge,
        ));
    }

    public function reportsAction()
    {
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Report');
    	$listReports = $repository->findBy(array('treated' => 0));
	    return $this->render('BFSiteBundle:Admin:reports.html.twig',array(
	    	'listReports' => $listReports,
	    	));
    }
    public function deleteVideoAction($id)
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

}
