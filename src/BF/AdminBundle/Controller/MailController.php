<?php

namespace BF\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MailController extends Controller
{
    public function newsletterAction(request $request)
    {
      //make a form to send a newsletter
      $form = $this->createFormBuilder()
            ->add('message', 'textarea')
            ->add('challengeMessage', 'textarea')
            ->add('subject', 'text')
            ->add('challenge', 'entity', array(
                  'class' => 'BFSiteBundle:Challenge',
                  'choice_label' => 'titleFR',
              ))
            ->add('save', 'submit', array('label' => 'Create Task'))
            ->getForm();



          $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {

            //getting the last 3 videos
            $listVideos = $this->getDoctrine()->getManager()->getRepository('BFSiteBundle:Video')->findBy(array(),array('date' => 'desc'),2,0);
            $listUsers = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('enabled' => 1));

            $data = $form->getData();

            $challenge = $data['challenge'];
            $challengeMessage = $data['challengeMessage'];
            $message = $data['message'];
            $subject = $data['subject'];


              // Send the mail to all the users
              foreach ($listUsers as $user) {
              //send the message to the user.
                  $message = \Swift_Message::newInstance()
                      ->setSubject($subject)
                      ->setFrom('noreply@bestfootball.fr')
                      ->setTo($user->getEmailCanonical())
                      ->setBody(
                        $this->renderView(
                          // app/Resources/views/Emails/newsletter.html.twig
                              'Emails/newsletter.html.twig',array(
                                'subject' => $subject,
                                'message' => $message,
                                'user' => $user,
                                'listVideos' => $listVideos,
                                'challenge' => $challenge,
                                'challengeMessage' => $challengeMessage
                              )
                        ),
                        'text/html'
                  );
                $this->get('swiftmailer.mailer.spool')->send($message); //using the spool mailing method
              }

              $request->getSession()->getFlashBag()->add('notice', 'The mail has been send!');
              return $this->redirect($this->generateUrl('bf_site_admin'));
          }


    

      return $this->render('BFAdminBundle:Mail:newsletter.html.twig',array(
        'form' => $form->createView(),
      ));
    }
}
