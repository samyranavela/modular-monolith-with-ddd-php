<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

final class AggregateNotFound extends \DomainException
{
    /**
     * @param class-string $aggregateClass
     */
    public function __construct(public readonly string $aggregateClass, public readonly string $id)
    {
        parent::__construct(\sprintf('%s "%s" not found.', $aggregateClass, $id));
    }
}
