<?php

namespace BF\RestApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BF\SiteBundle\Entity\Challenge;
use BF\SiteBundle\Form\ChallengeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChallengeController extends Controller
{
    /**
     * Collection get action
     * @var Request $request
     * @return array
     *
     * @Rest\View()
     */
    public function getAllAction(Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $listChallenges = $repository->findAll();
        $i = 0;
        foreach ($listChallenges as $challenge) {
         $id = $challenge->getId();
         $title = $challenge->getTitle();
         $challenges[$i]=array('id' => $id, 'title' => $title);
         $i++;
        }

        return array(
            'challenges' => $challenges,
            );
    }

    /**
     * Get entity instance
     * @var integer $id Id of the challenge
     * @return Challenge
     */
    protected function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BFSiteBundle:Challenge')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find challenge entity with id'.$id);
        }

        return $entity;
    }

    /**
     * Get action
     * @var integer $id Id of the challenge
     * @return array
     *
     * @Rest\View()
     */
    public function getAction($id)
    {
        $challenge = $this->getEntity($id);

        return array(
                'challenge' => $challenge,
                );
    }

    /**
     * Collection post action
     * @var Request $request
     * @return View|array
     */
    public function postAction(Request $request)
    {
        $challenge = new Challenge();
        $form = $this->createForm(new ChallengeType(), $challenge, array('csrf_protection' => false));
        $json_data = json_decode($request->getContent(),true);//get the response data as array
        $form->submit($json_data);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($challenge);
            $em->flush();

            $url = $this->generateUrl(
                'bf_rest_api_challenges_get',
                array('id' => $challenge->getId())
            );
            return View::createRedirect($url, Codes::HTTP_CREATED);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the challenge
     * @return View|array
     */
    public function putAction(Request $request, $id)
    {
        $challenge = $this->getEntity($id);
        $form = $this->createForm(new ChallengeType(), $challenge, array('csrf_protection' => false));
        $json_data = json_decode($request->getContent(),true);//get the response data as array
        $form->submit($json_data);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($challenge);
            $em->flush();

            return View::create(null, Codes::HTTP_NO_CONTENT);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the challenge
     * @return View
     */
    public function deleteAction($id)
    {
        $challenge = $this->getEntity($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($challenge);
        $em->flush();

        return View::create(null, Codes::HTTP_NO_CONTENT);
    }
}
