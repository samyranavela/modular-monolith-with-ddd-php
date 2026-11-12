<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

interface Announcements
{
    public function add(Announcement $announcement): void;

    public function exists(string $gadgetId): bool;

    /**
     * Issues the pending SQL now (the transaction middleware would only do it at the end of the command).
     */
    public function flush(): void;
}
