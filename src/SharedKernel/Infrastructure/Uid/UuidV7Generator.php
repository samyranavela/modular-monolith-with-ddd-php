<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\Uid;

use App\SharedKernel\Application\IdGenerator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Uid\Uuid;

#[AsAlias(IdGenerator::class)]
final class UuidV7Generator implements IdGenerator
{
    public function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
