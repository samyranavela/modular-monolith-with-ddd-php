<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversNothing]
final class KernelBootTest extends KernelTestCase
{
    #[Test]
    public function kernel_boots_in_test_environment(): void
    {
        $kernel = self::bootKernel();

        self::assertSame('test', $kernel->getEnvironment());
    }
}
