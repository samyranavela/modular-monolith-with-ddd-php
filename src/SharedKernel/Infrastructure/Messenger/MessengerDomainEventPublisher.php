<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Messenger;

use App\SharedKernel\Application\DomainEventPublisher;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsAlias(DomainEventPublisher::class)]
final readonly class MessengerDomainEventPublisher implements DomainEventPublisher
{
    public function __construct(#[Autowire(service: 'event.bus')] private MessageBusInterface $eventBus)
    {
    }

    public function publish(iterable $events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
