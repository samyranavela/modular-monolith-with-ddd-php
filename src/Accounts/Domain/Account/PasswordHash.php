<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidPasswordHashException;

final readonly class PasswordHash
{
    private function __construct(
        private string $hash,
    ) {
    }

    public static function fromString(string $hash): self
    {
        if ('' === $hash) {
            throw InvalidPasswordHashException::create();
        }

        return new self($hash);
    }

    public function isVerifiedBy(PasswordHasher $hasher, string $plain): bool
    {
        return $hasher->verify($plain, $this->hash);
    }
}
