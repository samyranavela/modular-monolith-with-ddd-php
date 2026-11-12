<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversNothing]
final class KernelBootTest extends KernelTestCase
{
    public function testKernelBootsInTestEnvironment(): void
    {
        $kernel = self::bootKernel();

        static::assertSame('test', $kernel->getEnvironment());
    }
}
