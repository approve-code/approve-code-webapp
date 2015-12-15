<?php

namespace ApproveCode\Bundle\UserBundle\Security\Core\User;

use Symfony\Component\EventDispatcher\Event;

use FOS\UserBundle\Model\UserInterface;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class UserProviderEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var UserResponseInterface
     */
    private $response;

    /**
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function __construct(UserInterface $user, UserResponseInterface $response)
    {
        $this->user = $user;
        $this->response = $response;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return UserResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}