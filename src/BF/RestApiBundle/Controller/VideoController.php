<?php

namespace BF\RestApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BF\SiteBundle\Entity\Video;
use BF\SiteBundle\Form\VideoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VideoController extends Controller
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
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video');
        $videos = $repository->findAll();

        return array(
            'videos' => $videos,
            );
    }

    /**
     * Get entity instance
     * @var integer $id Id of the video
     * @return Video
     */
    protected function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BFSiteBundle:Video')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find video entity with id '.$id);
        }

        return $entity;
    }

    /**
     * Get action
     * @var integer $id Id of the video
     * @return array
     *
     * @Rest\View()
     */
    public function getAction($id)
    {
        $video = $this->getEntity($id);

        return array(
                'video' => $video,
                );
    }

    /**
     * Collection post action
     * @var Request $request
     * @return View|array
     */
    public function postAction(Request $request)
    {
        $video = new Video();
        $form = $this->createForm(new VideoType(), $video, array('csrf_protection' => false));
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($video);
            $em->flush();

            $url = $this->generateUrl(
                'bf_rest_api_videos_get',
                array('id' => $video->getId())
            );
            return View::createRedirect($url, Response::HTTP_CREATED);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the video
     * @return View|array
     */
    public function putAction(Request $request, $id)
    {
        $video = $this->getEntity($id);
        $form = $this->createForm(new VideoType(), $video, array('csrf_protection' => false));
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($video);
            $em->flush();

            return  View::create(null, Response::HTTP_NO_CONTENT);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the video
     * @return View
     */
    public function deleteAction($id)
    {
        $video = $this->getEntity($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($video);
        $em->flush();

        return  View::create(null, Response::HTTP_NO_CONTENT);
    }
}
