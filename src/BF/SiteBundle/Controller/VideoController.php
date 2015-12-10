<?php

namespace BF\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Les entités
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Entity\Challenge;
use BF\UserBundle\Entity\User;
use BF\SiteBundle\Entity\VideoRepository;
//les types
use BF\SiteBundle\Form\VideoType;
use BF\SiteBundle\Form\VideoEditType;
use BF\SiteBundle\Form\ChallengeType;

class VideoController extends Controller
{
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

	    // On récupère $id de la video
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if (null === $video) {
	      throw new NotFoundHttpException("La video n'existe pas.");
	    }

	    return $this->render('BFSiteBundle:Video:view.html.twig', array(
	      'video'           => $video
	    ));
    }
    public function uploadAction(request $request, $id)
    {

    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
    	$challenge = $repository->findOneBy(array('id' => $id));
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	//we verify if the user already uploaded a video.
    	$repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
    	$check = $repository->checkChallenge($user, $challenge);
    	if (null != $check) {
    		 $this->addFlash('warning','You already participated at this challenge. You will have to delete your old video before uploading a new one.');
	       return $this->redirectToRoute('bf_site_challenges');
	    }

    	$gold = $challenge->getGold();
    	$silver = $challenge->getSilver();
    	$bronze = $challenge->getBronze();


	    // On crée un objet Video
	    $video = new Video();
	    $video
	    	->setDate(new \Datetime())
	    	->setUser($user)
	    	->setChallenge($challenge)
	    ;

	    $form = $this->get('form.factory')->create(new VideoType, $video);
	    
	    if ($form->handleRequest($request)->isValid()) {
	      $em = $this->getDoctrine()->getManager();

	      if($video->getRepetitions() >= $gold)
	      {
	      		$video->setScore('300');
	      }
	      if($gold > $video->getRepetitions() && $video->getRepetitions() >= $silver)
	      {
	      		$video->setScore('200');
	      }
	      if($silver > $video->getRepetitions() && $video->getRepetitions() >= $bronze)
	      {
	      		$video->setScore('100');
	      }
	      if($bronze > $video->getRepetitions())
	      {
	      		$video->setScore('0');
	      }

	      //now we update the points of the user

	      $points = $video->getScore() + $user->getPoints();
	      $user->setPoints($points);

	      $em->persist($user);
	      $em->persist($video);
	      $em->flush();


	      $this->addFlash('success', 'Your video was uploaded to our servers and you received '.$video->getScore().' points for this video.');

	      return $this->redirect($this->generateUrl('bf_site_video', array('id' => $video->getId())));
	    }

	    return $this->render('BFSiteBundle:Video:upload.html.twig', array(
	      'form' => $form->createView(),
	    ));
    }
    public function deleteAction(request $request, $id)
    {
	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video== null) {
	      throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $check = $video->getUser();
	    if ($check != $user) {
	      throw $this->createNotFoundException("You can't delete a video that isn't yours");
	    }
	    

	        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
		    // Cela permet de protéger la suppression d'annonce contre cette faille
		    $form = $this->createFormBuilder()->getForm();

		    if ($form->handleRequest($request)->isValid()) {


		      $points =  $user->getPoints() - $video->getScore();
	      	  $user->setPoints($points);
	      	  $em->persist($user);
		      $em->remove($video);
		      $em->flush();

		      $request->getSession()->getFlashBag()->add('success', "Your video has been deleted.");

		      return $this->redirect($this->generateUrl('bf_site_videos'));
		    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:delete.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView()
		    ));
    }
    public function modifyAction(request $request, $id)
    {
	    $em = $this->getDoctrine()->getManager();
	    $video = $em->getRepository('BFSiteBundle:Video')->find($id);

	    if ($video== null) {
	      throw $this->createNotFoundException("The video n°".$id." doesn't exist.");
	    }

	    $user = $this->container->get('security.context')->getToken()->getUser();
	    $check = $video->getUser();
	    if ($check != $user) {
	      throw $this->createNotFoundException("You can't modify a video that isn't yours");
	    }
	    
		    $form = $this->get('form.factory')->create(new VideoEditType, $video);
		    if ($form->handleRequest($request)->isValid()) {

		      $em->persist($video);
		      $em->flush();

		      $request->getSession()->getFlashBag()->add('success', "Your video has been modified.");

		      return $this->redirect($this->generateUrl('bf_site_videos'));
		    }

		    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		    return $this->render('BFSiteBundle:Video:modify.html.twig', array(
		      'video' => $video,
		      'form'   => $form->createView()
		    ));
    }
}
