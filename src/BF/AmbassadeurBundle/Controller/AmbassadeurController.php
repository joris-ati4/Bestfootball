<?php

namespace BF\AmbassadeurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

//Les entités
use BF\SiteBundle\Entity\Challenge;
//les types
use BF\SiteBundle\Form\Type\ChallengeAmbassadeurType;
use BF\SiteBundle\Form\Type\ChallengeAmbassadeurEditType;
use BF\SiteBundle\Form\Type\ChallengeDelType;

class AmbassadeurController extends Controller
{
    public function indexAction()
    {
	   $user = $this->container->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findByType($user->getUsername());


        return $this->render('BFAmbassadeurBundle::index.html.twig',array(
            'listChallenges' => $listChallenges,
            ));
    }
    public function viewChallengeAction($id)
    {
        //get the challenge and check if he can view the videos.
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);
        $user = $this->container->get('security.context')->getToken()->getUser();
        if($challenge->getType() != $user->getUsername()){
            //the user can't view the challenge.
            throw new AccessDeniedException();
        }

        $listVideos = $challenge->getVideos();

        return $this->render('BFAmbassadeurBundle:Challenge:view.html.twig',array(
            'listVideos' => $listVideos,
            'challenge' => $challenge,
            ));
    }

    public function addChallengeAction(request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        // On crée un objet Challenge
        $challenge = new Challenge();
        $challenge
            ->setType($user->getUsername())
            ->setPartner(0)
            ->setOne('0')
            ->setTwo('0')
            ->setThree('0')
            ->setFour('0')
            ->setFive('0')
            ->setSix('0')
        ;

        $form = $this->get('form.factory')->create(new ChallengeAmbassadeurType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {

          $title = $challenge->getTitle();
          $challenge->setTitle($user->getUsername().' - '.$title);           
          $em = $this->getDoctrine()->getManager();
          $em->persist($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

          return $this->redirect($this->generateUrl('bf_ambassadeur_index'));
        }

        return $this->render('BFAmbassadeurBundle:Challenge:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    public function modChallengeAction(request $request, $id)
    {
        // On crée un objet Challenge
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);;

        $form = $this->get('form.factory')->create(new ChallengeAmbassadeurEditType, $challenge);
        
        if ($form->handleRequest($request)->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $em->persist($challenge);
          $em->flush();

          $request->getSession()->getFlashBag()->add('notice', 'The new challenge has been registered');

          return $this->redirect($this->generateUrl('bf_ambassadeur_index'));
        }

        return $this->render('BFAmbassadeurBundle:Challenge:mod.html.twig', array(
          'form' => $form->createView(),
          'challenge' => $challenge,
        ));
    }
    public function delChallengeAction(request $request, $id)
    {
        // On crée un objet Challenge
        $challenge = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge')->find($id);

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

        return $this->render('BFAmbassadeurBundle:Challenge:del.html.twig', array(
          'form' => $form->createView(),
          'challenge' => $challenge,
        ));
    }
    public function downloadVideoAction(request $request, $id)
    {
        $video = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->find($id);

        $filename = 'Video_'.$video->getUser()->getUsername().'.mp4';
        $path = '/var/www/bestfootball.fr/current/web/uploads/videos/'.$video->getSource();

        $response = new Response();
        $response->setContent(file_get_contents($path));
        $response->headers->set('Content-Type', 'application/force-download'); // modification du content-type pour forcer le téléchargement (sinon le navigateur internet essaie d'afficher le document)
        $response->headers->set('Content-disposition', 'filename='. $filename);
         
        return $response;
    }
}
