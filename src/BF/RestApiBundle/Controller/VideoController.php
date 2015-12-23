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

        //we retrieve the data from the JSON that is received.
        $file = $request->files->get('file');
        $json_data = $request->files->get('data');
        $json_data = file_get_contents($json_data);
        $data = json_decode($json_data,true);

        //We stock the data from the JSON in different variables
        $idChallenge = $data['idChallenge'];
        $idUser = $data['idUser'];
        $title = $data['title'];
        $description = $data['description'];
        $repetitions = $data['repetitions'];

        //We search the user and challenge into the database (need to add security if these values are not in the database)
        $repository = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Challenge');
        $challenge = $repository->find($idChallenge);
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $user = $repository->find($idUser);

        // On crée un objet Video
        $video = new Video();
        $video
            ->setDate(new \Datetime())
            ->setUser($user)
            ->setChallenge($challenge)
            ->setRepetitions($repetitions)
            ->setTitle($title)
            ->setDescription($description)
            ->setFile($file)
        ;

        //getting the different values for gold,silver and bronze. and setting the points for the video
        $gold = $challenge->getGold();
        $silver = $challenge->getSilver();
        $bronze = $challenge->getBronze();

        if($video->getRepetitions() >= $gold){$video->setScore('300');}
        if($gold > $video->getRepetitions() && $video->getRepetitions() >= $silver){$video->setScore('200');}
        if($silver > $video->getRepetitions() && $video->getRepetitions() >= $bronze){$video->setScore('100');}
        if($bronze > $video->getRepetitions()){$video->setScore('0');}

        //retrieving the points from the video and updating the points off the user.
        $points = $video->getScore() + $user->getPoints();
        $user->setPoints($points);

        //all the informations are set, we now proceed to convert the video etc.
        $em = $this->getDoctrine()->getManager();
        $em->persist($video);
        $em->persist($user);
        $em->flush();

        $url = $this->generateUrl(
            'bf_rest_api_videos_get',
            array('id' => $video->getId())
        );
        return View::createRedirect($url, Response::HTTP_CREATED);
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
        $json_data = json_decode($request->getContent(),true);//get the response data as array
        $form->submit($json_data);

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
