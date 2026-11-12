<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Creates the sandbox tables on demand: they exist for the tests only, so no migration ships them.
 * Only the sandbox is mapped in the test environment, which makes the whole metadata set the sandbox's.
 */
final class SandboxSchema
{
    public static function ensure(EntityManagerInterface $entityManager): void
    {
        (new SchemaTool($entityManager))->updateSchema($entityManager->getMetadataFactory()->getAllMetadata());
    }
}
