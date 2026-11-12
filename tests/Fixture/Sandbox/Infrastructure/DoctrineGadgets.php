<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Infrastructure;

use App\SharedKernel\Domain\AggregateNotFound;
use App\Tests\Fixture\Sandbox\Domain\Gadget;
use App\Tests\Fixture\Sandbox\Domain\Gadgets;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineGadgets implements Gadgets
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Gadget $gadget): void
    {
        $this->entityManager->persist($gadget);
    }

    public function get(string $id): Gadget
    {
        return $this->entityManager->find(Gadget::class, $id) ?? throw new AggregateNotFound(Gadget::class, $id);
    }
}
