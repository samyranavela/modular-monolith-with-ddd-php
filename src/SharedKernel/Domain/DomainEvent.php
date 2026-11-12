<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

/**
 * Something that happened in an aggregate, named in the past tense. Handled synchronously, in the
 * transaction of the command that raised it; never serialised (an integration event is another contract).
 */
interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
