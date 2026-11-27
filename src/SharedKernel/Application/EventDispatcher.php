<?php

declare(strict_types=1);

namespace App\SharedKernel\Application;

use App\SharedKernel\Domain\DomainEvent;

interface EventDispatcher
{
    public function dispatch(DomainEvent ...$events): void;
}
