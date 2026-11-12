<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Application;

use App\Tests\Fixture\Sandbox\Domain\Announcement;
use App\Tests\Fixture\Sandbox\Domain\Announcements;
use App\Tests\Fixture\Sandbox\Domain\GadgetRegistered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Reference domain event handler: runs synchronously inside the transaction of RegisterGadget.
 */
#[AsMessageHandler(bus: 'event.bus')]
final readonly class AnnounceGadgetWhenRegistered
{
    public function __construct(private Announcements $announcements)
    {
    }

    public function __invoke(GadgetRegistered $event): void
    {
        $this->announcements->add(new Announcement($event->gadgetId));

        if ('explode' === $event->name) {
            // Issue the SQL first, so the rollback is shown to undo executed statements, not an unflushed unit of work.
            $this->announcements->flush();

            throw new \RuntimeException('The reaction failed.');
        }
    }
}
