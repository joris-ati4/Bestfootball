<?php

namespace BF\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UserController extends Controller
{
    public function getUsersAction($page)
    {
        if ($page < 1) {
          throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        $nbPerPage = 30;

        // On récupère notre objet Paginator
        $listUsers = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->getUsers($page, $nbPerPage);

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listUsers)/$nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
          throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('BFUserBundle:Users:list.html.twig', array(
          'listUsers'   => $listUsers,
          'nbPages'     => $nbPages,
          'page'        => $page
        ));
    }
}
