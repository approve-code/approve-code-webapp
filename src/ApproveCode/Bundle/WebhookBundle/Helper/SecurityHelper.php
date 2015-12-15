<?php

namespace ApproveCode\Bundle\WebhookBundle\Helper;

class SecurityHelper
{
    const SHA1_ALGORITHM = 'sha1';

    /**
     * @var string
     */
    private $webhookSecert;

    /**
     * @param string $webhookSecret
     */
    public function __construct($webhookSecret)
    {
        $this->webhookSecert = $webhookSecret;
    }

    /**
     * Check
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function checkSha1Signature($payload, $signature)
    {
        $calculatedHash = hash_hmac(self::SHA1_ALGORITHM, $payload, $this->webhookSecert);
        return hash_equals($calculatedHash, $signature);
    }
}
