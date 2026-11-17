<?php

declare(strict_types=1);

namespace Academia\Services;

interface WhatsAppGatewayInterface
{
    public function status(): array;

    public function send(string $key, string $phone, string $message): array;
}
