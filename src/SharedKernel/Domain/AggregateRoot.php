<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

/**
 * Pure base class: records domain events, knows nothing about Doctrine or Messenger. The command handler
 * pulls the events after saving and publishes them explicitly.
 */
abstract class AggregateRoot
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    /**
     * @return list<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    protected function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    protected static function checkRule(BusinessRule $rule): void
    {
        if ($rule->isBroken()) {
            throw new BusinessRuleViolation($rule);
        }
    }
}
