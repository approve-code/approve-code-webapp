<?php

namespace ApproveCode\Bundle\UserBundle\Security\Core\User;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;

class UserProvider extends FOSUBUserProvider
{
    const USER_REGISTRATION_EVENT = 'ac_user_registration_event';
    const USER_LOGIN_EVENT = 'ac_user_login_event';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = parent::loadUserByOAuthUserResponse($response);

            if ($this->eventDispatcher) {
                $event = new UserProviderEvent($user, $response);
                $this->eventDispatcher->dispatch(self::USER_LOGIN_EVENT, $event);
                $this->userManager->updateUser($user);
            }
        } catch (AccountNotLinkedException $e) {
            $user = $this->userManager->createUser();
            $user->setUsername($response->getNickname());
            $user->setEmail($response->getEmail());
            $user->setPlainPassword($response->getAccessToken());
            $user->setEnabled(true);

            if ($this->eventDispatcher) {
                $event = new UserProviderEvent($user, $response);
                $this->eventDispatcher->dispatch(self::USER_REGISTRATION_EVENT, $event);
            }

            $this->userManager->updateUser($user);
        }

        return $user;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
