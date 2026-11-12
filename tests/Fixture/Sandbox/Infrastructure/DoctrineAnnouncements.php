<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Infrastructure;

use App\Tests\Fixture\Sandbox\Domain\Announcement;
use App\Tests\Fixture\Sandbox\Domain\Announcements;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAnnouncements implements Announcements
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Announcement $announcement): void
    {
        $this->entityManager->persist($announcement);
    }

    public function exists(string $gadgetId): bool
    {
        return null !== $this->entityManager->find(Announcement::class, $gadgetId);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
