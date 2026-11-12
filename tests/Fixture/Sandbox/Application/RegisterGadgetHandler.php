<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Application;

use App\SharedKernel\Application\DomainEventPublisher;
use App\Tests\Fixture\Sandbox\Domain\Gadget;
use App\Tests\Fixture\Sandbox\Domain\Gadgets;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Reference command handler: build the aggregate, save it, publish its domain events explicitly.
 * No flush here: the command bus transaction middleware flushes and commits.
 */
#[AsMessageHandler(bus: 'command.bus')]
final readonly class RegisterGadgetHandler
{
    public function __construct(private Gadgets $gadgets, private DomainEventPublisher $events)
    {
    }

    public function __invoke(RegisterGadget $command): void
    {
        $gadget = Gadget::register($command->id, $command->name);

        $this->gadgets->add($gadget);

        $this->events->publish($gadget->pullDomainEvents());
    }
}
