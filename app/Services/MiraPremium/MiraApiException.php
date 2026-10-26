<?php

namespace App\Services\MiraPremium;

/**
 * A tagged, closed-set API error for the Mira Premium endpoints. The JSON
 * body is always {"error": {"code": "<Code>", "message": "...", ...meta}},
 * so the page can handle every case by its code (see CODES).
 */
class MiraApiException extends \RuntimeException
{
    /** Code => HTTP status. The page's decoder knows exactly these. */
    public const CODES = [
        'InvalidUrl'       => 422,
        'InvalidCaptcha'   => 422,
        'InvalidInput'     => 422,
        'NotEntitled'      => 403,
        'QuotaExceeded'    => 429,
        'LimitReached'     => 409,
        'InvalidSignature' => 401,
        'Expired'          => 410,
        'NotFound'         => 404,
        'SiteUnreachable'  => 422,
        'Unavailable'      => 503,
    ];

    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $meta = [],
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return self::CODES[$this->errorCode] ?? 400;
    }

    public function toArray(): array
    {
        return ['error' => ['code' => $this->errorCode, 'message' => $this->getMessage()] + $this->meta];
    }
}
