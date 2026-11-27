<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidPasswordHashException;
use App\Accounts\Domain\Account\PasswordHash;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PasswordHashTest extends TestCase
{
    #[Test]
    public function a_password_hash_accepts_any_non_empty_string(): void
    {
        $this->expectNotToPerformAssertions();

        PasswordHash::fromString('hash');
    }

    #[Test]
    public function a_password_hash_is_verified_by_the_hasher_with_the_plain_password(): void
    {
        $hasher = new FakePasswordHasher();
        $hash = PasswordHash::fromString('hashed:secret');

        self::assertTrue($hash->isVerifiedBy($hasher, 'secret'));
        self::assertFalse($hash->isVerifiedBy($hasher, 'another'));
    }

    #[Test]
    public function a_password_hash_cannot_be_empty(): void
    {
        $this->expectException(InvalidPasswordHashException::class);

        PasswordHash::fromString('');
    }
}
