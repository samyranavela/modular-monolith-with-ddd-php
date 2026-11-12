<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

/**
 * What the reaction to GadgetRegistered leaves behind: proves a domain event was processed.
 */
final readonly class Announcement
{
    public function __construct(public string $gadgetId)
    {
    }
}
