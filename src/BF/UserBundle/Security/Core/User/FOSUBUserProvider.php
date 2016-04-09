<?php
namespace BF\UserBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use BF\SiteBundle\Entity\Media;

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
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        //when the user is registrating
        if (null === $user) {

            $service = $response->getResourceOwner()->getName();


            
            $mail = $response->getEmail();
            $firstname = $response->getFirstname();
            $lastname = $response->getLastname();
            $nickname = $firstname.rand(0, 1000000);
            $data = $response->getResponse();
            $gender = $data['gender'];
            $birthday = $data['birthday'];
            

            //we check for the email existence - if so, throw error.
            if($this->userManager->findUserByEmail($response->getEmail())){
                $message = 'There is already an account with this email address';
                throw new \Symfony\Component\Security\Core\Exception\AuthenticationException($message);
            }
            while($this->userManager->findUserByUsername($nickname)){ //their is already a user with this username
                $nickname = $firstname.rand(0, 1000000);
            }
            $birthday = new \DateTime($birthday);

            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());
            //I have set all requested data with the user's username

            //creating the picture entity for the new user
            $picture = new Media();
            $picture
              ->setPath('/uploads/img/profile.png')
              ->setName('default profile picture on bestfootball')
              ->setImage('/uploads/img/profile.png')
              ->setOriginalImage('/uploads/img/profile.png')
            ;

            $message = 'Welcome to bestfootball. Please complete your personal informations by clicking on this notification or by going to the informations section. Once that is all set up, you can go out there and show your skills!';
            $link = $this->generateUrl('bf_site_settings');
            $service = $this->container->get('bf_site.notification');
            $notification = $service->create($user, $message, null, $link);

            //profile picture for facebook
            if($service == 'facebook'){
                $profilepicture = $response->getProfilePicture();
                $picture
                    ->setPath($profilepicture)
                    ->setName('Profile picture of '.$username.' on Bestfootball.fr')
                    ->setImage($profilepicture)
                    ->setOriginalImage($profilepicture)
                    ;
            }

            //modify here with relevant data
            $user->setUsername($nickname);
            $user->setEmail($mail);
            $user->setPlainPassword($username);
            $user->setEnabled(true);
            $user->setPoints(0);
            $user->setDuelPoints(0);
            $user->setDuelWins(0);
            $user->setMedia($picture);
            $user->setName($lastname);
            $user->setFirstname($firstname);
            $user->setGender($gender);
            $user->setBirthday($birthday);
            $this->userManager->updateUser($user);

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
