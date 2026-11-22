<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain\Account;

use App\Accounts\Domain\Account\PasswordHasher;

final class FakePasswordHasher implements PasswordHasher
{
    public function hash(string $plain): string
    {
        return 'hashed:'.$plain;
    }

    public function verify(string $plain, string $hash): bool
    {
        return $hash === $this->hash($plain);
    }
}
