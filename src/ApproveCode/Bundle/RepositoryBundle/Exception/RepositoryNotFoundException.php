<?php

namespace ApproveCode\Bundle\RepositoryBundle\Exception;

class RepositoryNotFoundException extends \RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Repository not found.', \Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
