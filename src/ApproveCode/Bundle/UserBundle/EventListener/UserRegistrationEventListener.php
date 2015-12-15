<?php

namespace ApproveCode\Bundle\UserBundle\EventListener;

use ApproveCode\Bundle\UserBundle\Entity\User;
use ApproveCode\Bundle\UserBundle\Security\Core\User\UserProviderEvent;

class UserRegistrationEventListener
{
    /**
     * @param UserProviderEvent $event
     */
    public function onRegistration(UserProviderEvent $event)
    {
        $user = $event->getUser();

        if ($user instanceof User) {
            $response = $event->getResponse();
            $user->setAccessToken($response->getAccessToken());
            $user->setGithubID($response->getUsername());
        }
    }

    /**
     * @param UserProviderEvent $event
     */
    public function onLogin(UserProviderEvent $event)
    {
        $user = $event->getUser();

        if ($user instanceof User) {
            $response = $event->getResponse();
            $user->setAccessToken($response->getAccessToken());
        }
    }
}
