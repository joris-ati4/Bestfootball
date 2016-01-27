<?php

// src/BF/RestApiBundle/OAuth/FacebookGrantExtension.php

namespace BF\RestApiBundle\OAuth;

use Doctrine\Common\Persistence\ObjectRepository;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

/**
 * Play at bingo to get an access_token: May the luck be with you!
 */
class FacebookGrantExtension implements GrantExtensionInterface
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
        $user = $this->userRepository->findBy(array('facebook_id' => $inputData['id']),array());
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
