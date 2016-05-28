<?php 
// src/Acme/DemoBundle/Command/GreetCommand.php 
namespace BF\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateUserCommand extends ContainerAwareCommand 
{ 

	protected function configure() 
	{ 
		$this ->setName('Activate:Users') 
		->setDescription('envoyer les mails de confirmation à tous les utilisateurs et activer leur compte.')
		; 
	} 

	protected function execute(InputInterface $input, OutputInterface $output) 
	{
		//retrieve all the not finished duels
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
	    $listUsers = $em->getRepository('BFUserBundle:User')->findBy(array("enabled" => 0));

	    if(!$listUsers){
	    	$output->writeln("Tous les comptes sont activés");
	    }
	    else{
	    	foreach ($listUsers as $user) {
	    		
	    		//activate the user.

	    		$user->setEnabled(1);
	    		$em->persist($user);
	    		$em->flush();

	    		//send the message to the user.

                $secondmessage = \Swift_Message::newInstance()
                    ->setSubject("Activation de votre compte Bestfootball")
                    ->setFrom('noreply@bestfootball.fr')
                    ->setTo($user->getEmailCanonical())
                    ->setBody(
                    	$this->getContainer()->get('templating')->render(
                    		// app/Resources/views/Emails/registration.html.twig
                            'Emails/activation.html.twig',
                            array('user' => $user)
                    	),
                        'text/html'
                    )
	            ;
	            $this->getContainer()->get('mailer')->send($secondmessage);

	            $output->writeln("mail envoyé à ".$user->getUsername()." !");
	    	}
		}
	}
}
