<?php

namespace App\Exceptions;

class BrevoRateLimitedException extends \RuntimeException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct("Brevo API rate limit reached, retry after {$retryAfterSeconds}s.");
    }
}
