<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

use App\SharedKernel\Domain\DomainEvent;

final readonly class GadgetRegistered implements DomainEvent
{
    public \DateTimeImmutable $occurredOn;

    public function __construct(public string $gadgetId, public string $name)
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
