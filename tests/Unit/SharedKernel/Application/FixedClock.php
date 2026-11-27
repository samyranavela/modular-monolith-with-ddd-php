<?php

declare(strict_types=1);

namespace App\Tests\Unit\SharedKernel\Application;

use App\SharedKernel\Application\Clock;

final readonly class FixedClock implements Clock
{
    public function __construct(private \DateTimeImmutable $now)
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
