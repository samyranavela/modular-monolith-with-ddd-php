<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Application;

use App\SharedKernel\Domain\DomainEvent;

/**
 * A domain event nobody subscribes to: publishing it is not an error.
 */
final readonly class UnobservedEvent implements DomainEvent
{
    public function occurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
