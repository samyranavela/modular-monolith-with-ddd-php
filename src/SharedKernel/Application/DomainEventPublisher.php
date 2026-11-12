<?php

declare(strict_types=1);

namespace App\SharedKernel\Application;

use App\SharedKernel\Domain\DomainEvent;

/**
 * Port used by command handlers to publish the domain events of a saved aggregate. Synchronous: the
 * reactions run in the transaction of the command and succeed or fail with it.
 */
interface DomainEventPublisher
{
    /**
     * @param iterable<DomainEvent> $events
     */
    public function publish(iterable $events): void;
}
