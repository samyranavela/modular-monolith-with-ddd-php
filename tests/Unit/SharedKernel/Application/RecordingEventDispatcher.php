<?php

declare(strict_types=1);

namespace App\Tests\Unit\SharedKernel\Application;

use App\SharedKernel\Application\EventDispatcher;
use App\SharedKernel\Domain\DomainEvent;

final class RecordingEventDispatcher implements EventDispatcher
{
    /** @var list<DomainEvent> */
    private array $dispatched = [];

    public function dispatch(DomainEvent ...$events): void
    {
        array_push($this->dispatched, ...$events);
    }

    /**
     * @return list<DomainEvent>
     */
    public function dispatched(): array
    {
        return $this->dispatched;
    }
}
