<?php
namespace BF\UserBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManager;

use BF\SiteBundle\Entity\Picture;

class FOSUBUserProvider extends BaseClass
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $mail = $response->getEmail();
        $firstname = $response->getFirstname();
        $lastname = $response->getLastname();
        $gender = $response->getGender();
        $birthday = $response->getBirthday();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        //when the user is registrating
        if (null === $user) {

            $service = $response->getResourceOwner()->getName();

            //we check for the email existence - if so, throw error.
            if($existent_user = $this->userManager->findUserByEmail($response->getEmail())){
                $message = 'There is already an account with this email address';
                throw new \Symfony\Component\Security\Core\Exception\AuthenticationException($message);
            }

            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            //I have set all requested data with the user's username

            //creating the picture entity for the new user
            $picture = new Picture();
            $picture
              ->setSrc('profile.png')
              ->setAlt('default profile picture on bestfootball')
            ;

            //profile picture for facebook
            if($service == 'facebook'){
                $profilepicture = $response->getProfilePicture();
                $picture->setSrc($profilepicture)->setAlt('Profile picture of '.$username.' on Bestfootball.fr');
            }

            //modify here with relevant data
            $user->setUsername($firstname.'.'$lastname);
            $user->setEmail($mail);
            $user->setPassword($username);
            $user->setEnabled(true);
            $user->setPoints(0);
            $user->setDuelPoints(0);
            $user->setPicture($picture);
            $user->setName($lastname);
            $user->setFirstname($firstname);
            $user->setGender($gender);
            $user->setBirthday($birthday)
            $this->userManager->updateUser($user);

            //we persist the picture and flush it.
            //$em->persist($picture);
            //$em->flush();

            return $user;
        }
        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());
        return $user;
    }
}
