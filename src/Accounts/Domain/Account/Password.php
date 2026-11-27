<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidPasswordException;

final readonly class Password
{
    private const int MINIMUM_LENGTH = 15;
    private const int MAXIMUM_LENGTH = 128;

    private function __construct(
        private string $password,
    ) {
    }

    public static function create(string $password): self
    {
        if (!mb_check_encoding($password, 'UTF-8')) {
            throw InvalidPasswordException::create();
        }

        $passwordLength = mb_strlen($password, 'UTF-8');
        if ($passwordLength < self::MINIMUM_LENGTH || $passwordLength > self::MAXIMUM_LENGTH) {
            throw InvalidPasswordException::create();
        }

        return new self($password);
    }

    public function hashWith(PasswordHasher $hasher): PasswordHash
    {
        return PasswordHash::fromString($hasher->hash($this->password));
    }

    public function matches(PasswordHash $hash, PasswordHasher $hasher): bool
    {
        return $hash->isVerifiedBy($hasher, $this->password);
    }
}
