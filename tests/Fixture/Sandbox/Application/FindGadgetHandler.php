<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Application;

use App\Tests\Fixture\Sandbox\Domain\Announcements;
use App\Tests\Fixture\Sandbox\Domain\Gadgets;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Reference query handler: returns a view, never an aggregate.
 */
#[AsMessageHandler(bus: 'query.bus')]
final readonly class FindGadgetHandler
{
    public function __construct(private Gadgets $gadgets, private Announcements $announcements)
    {
    }

    public function __invoke(FindGadget $query): GadgetView
    {
        $gadget = $this->gadgets->get($query->id);

        return new GadgetView($gadget->id(), $gadget->name(), $this->announcements->exists($query->id));
    }
}
