<?php

declare(strict_types=1);

namespace Academia\Services;

final class AccessDecision
{
    public function __construct(public readonly string $status, public readonly string $title, public readonly string $message, public readonly bool $allowed)
    {
    }
}
