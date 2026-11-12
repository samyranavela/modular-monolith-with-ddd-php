<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Run by `composer schema` (test environment) between the migrations and the schema validation, so the
 * validation sees the sandbox tables the test-only mapping expects.
 */
#[AsCommand(name: 'sandbox:schema:create', description: 'Create the test-only sandbox tables (test environment)')]
final class CreateSandboxSchemaCommand
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(): int
    {
        SandboxSchema::ensure($this->entityManager);

        return Command::SUCCESS;
    }
}
