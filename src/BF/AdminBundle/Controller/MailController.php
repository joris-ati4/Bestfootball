<?php

namespace BF\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MailController extends Controller
{
    public function newsletterChallengeAction(request $request)
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
                      ->setFrom(array('noreply@bestfootball.fr' => 'Bestfootball'))
                      ->setTo($user->getEmailCanonical())
                      ->setBody(
                        $this->renderView(
                          // app/Resources/views/Emails/newsletter.html.twig
                              'Emails/newsletter.html.twig',array(
                                'subject' => $subject,
                                'message' => $message,
                                'user' => $user,
                                'challenge' => $challenge,
                              )
                        ),
                        'text/html'
                  );
                $this->get('swiftmailer.mailer.spool')->send($message); //using the spool mailing method
                unset($message); //resetting the memory variable back to null
              }

              $request->getSession()->getFlashBag()->add('notice', 'The mail has been send!');
              return $this->redirect($this->generateUrl('bf_site_admin'));
          }


    

      return $this->render('BFAdminBundle:Mail:newsletter.html.twig',array(
        'form' => $form->createView(),
      ));
    }
    public function newsletterLastVideoAction(request $request)
    {
      //make a form to send a newsletter
      $form = $this->createFormBuilder()
            ->add('subject', 'text')
            ->add('message', 'textarea')
            
            ->add('video', 'entity', array(
                  'class' => 'BFSiteBundle:Video',
                  'choice_label' => 'id',
              ))
            ->add('test', 'checkbox', array('required' => false))
            ->add('save', 'submit', array('label' => 'Envoyer la newsletter'))
            ->getForm();

            



          $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {

            //getting the users for the newsletter
            $listUsers = $this->getDoctrine()->getManager()->getRepository('BFUserBundle:User')->findBy(array('mailWeekly' => 1));

            $data = $form->getData();

            $video = $data['video'];
            $message = $data['message'];
            $subject = $data['subject'];

            //generate random 64 bit client id
            $bytes = random_bytes(64);
            $clientID = bindec($bytes);

            if($data['test'] === true){
              //We send a test mail to my personal email.
              $user = $this->container->get('security.context')->getToken()->getUser();
              $mail = \Swift_Message::newInstance()
                      ->setSubject($subject)
                      ->setFrom(array('noreply@bestfootball.fr' => 'Bestfootball'))
                      ->setTo($user->getEmailCanonical())
                      ->setBody(
                        $this->renderView(
                          // app/Resources/views/Emails/newsletter.html.twig
                              'Emails/newsletter/lastvideo.html.twig',array(
                                'subject' => $subject,
                                'message' => $message,
                                'user' => $user,
                                'video' => $video,
                                'clientID' => $clientID
                              )
                        ),
                        'text/html'
                  );
                $this->get('swiftmailer.mailer')->send($mail); 
            }
            else{
              // Send the mail to all the users
              foreach ($listUsers as $user) {

                //generate random 64 bit client id
                $bytes = random_bytes(64);
                $clientID = bindec($bytes);
                
                //send the message to the user.
                  $mail = \Swift_Message::newInstance()
                      ->setSubject($subject)
                      ->setFrom(array('noreply@bestfootball.fr' => 'Bestfootball'))
                      ->setTo($user->getEmailCanonical())
                      ->setBody(
                        $this->renderView(
                          // app/Resources/views/Emails/newsletter.html.twig
                              'Emails/newsletter/lastvideo.html.twig',array(
                                'subject' => $subject,
                                'message' => $message,
                                'user' => $user,
                                'video' => $video,
                                'clientID' => $clientID
                              )
                        ),
                        'text/html'
                  );
                $this->get('swiftmailer.mailer.spool')->send($mail); //using the spool mailing method
                unset($mail); //resetting the memory variable back to null
              }
            }
              $request->getSession()->getFlashBag()->add('notice', 'The mail has been send!');
              return $this->redirect($this->generateUrl('bf_site_admin'));
          }


    

      return $this->render('BFAdminBundle:Mail:newsletterLastVideo.html.twig',array(
        'form' => $form->createView(),
      ));
    }
}
