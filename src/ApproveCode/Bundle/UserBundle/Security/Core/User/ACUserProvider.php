<?php

namespace ApproveCode\Bundle\UserBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;

class ACUserProvider extends FOSUBUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = parent::loadUserByOAuthUserResponse($response);
        } catch (AccountNotLinkedException $e) {
            $property = $this->getProperty($response);

            $user = $this->userManager->createUser();
            $user->setUsername($response->getNickname());
            $user->setEmail($response->getEmail());
            $user->setPlainPassword($response->getAccessToken());
            $user->setEnabled(true);
            $this->accessor->setValue($user, $property, $response->getUsername());
            $this->userManager->updateUser($user);
        }

        return $user;
    }
}
