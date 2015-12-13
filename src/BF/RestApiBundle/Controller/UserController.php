<?php

namespace BF\RestApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BF\UserBundle\Entity\User;
use BF\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
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
        $repository = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User');
        $users = $repository->findAll();

        return array(
            'users' => $users,
            );
    }

    /**
     * Get entity instance
     * @var integer $id Id of the user
     * @return User
     */
    protected function getEntity($username)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BFUserBundle:User')->findByUsername($username);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find user entity with username '.$username);
        }

        return $entity;
    }

    /**
     * Get action
     * @var integer $id Id of the user
     * @return array
     *
     * @Rest\View()
     */
    public function getAction($username)
    {
        $user = $this->getEntity($username);

        $id=$user[0]->getId();
        $username=$user[0]->getUsername();
        $salt=$user[0]->getSalt();
        $password=$user[0]->getPassword();

        $user =array('id' => $id,'username' => $username,'password' => $password,'salt' => $salt);



        return  array(
            'user' => $user,
        );
    }

    /**
     * Collection post action
     * @var Request $request
     * @return View|array
     */
    public function postAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user, array('csrf_protection' => false));
        $json_data = json_decode($request->getContent(),true);//get the response data as array
        $form->submit($json_data);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            $url = $this->generateUrl(
                'bf_rest_api_users_get',
                array('id' => $user->getId())
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
     * @var integer $id Id of the user
     * @return View|array
     */
    public function putAction(Request $request, $id)
    {
        $user = $this->getEntity($id);
        $form = $this->createForm(new UserType(), $user, array('csrf_protection' => false));
        $json_data = json_decode($request->getContent(),true);//get the response data as array
        $form->submit($json_data);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return View::createRedirect($url, Codes::HTTP_NO_CONTENT);
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Delete action
     * @var integer $id Id of the user
     * @return View
     */
    public function deleteAction($id)
    {
        $user = $this->getEntity($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return View::create(null, Codes::HTTP_NO_CONTENT);
    }
}
