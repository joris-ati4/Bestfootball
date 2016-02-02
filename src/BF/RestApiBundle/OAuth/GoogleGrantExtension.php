<?php

// src/BF/RestApiBundle/OAuth/GoogleGrantExtension.php

namespace BF\RestApiBundle\OAuth;

use Doctrine\Common\Persistence\ObjectRepository;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

/**
 * Play at bingo to get an access_token: May the luck be with you!
 */
class GoogleGrantExtension implements GrantExtensionInterface
{

    //the User repositroy located at BF/UserBundle/Entity/User
    private $userRepository;

    public function __construct(ObjectRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /*
     * {@inheritdoc}
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        //retrieving the user object.
        $user = $this->userRepository->findOneBy(array('google_id' => $inputData['id']),array());

        if(!$user){
            throw $this->createNotFoundException('this user does not exist.');
        }
        
        $cryptedpassword = $user->getPassword();

        if (password_verify($inputData['password'], $cryptedpassword)) {
            //if you need to return access token with associated user
            return array(
                'data' => $user
            );

            //if you need an anonymous user token
            //return true;
        }

        return false;
    }
}
