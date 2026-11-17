<?php

declare(strict_types=1);

namespace Academia\Services;

final class WhatsAppGatewayException extends \RuntimeException
{
    public function __construct(string $message, public readonly bool $uncertain = true)
    {
        parent::__construct($message);
    }
}
