<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidLoginException;

final readonly class Login
{
    private const int MIN_LENGTH = 3;
    private const int MAX_LENGTH = 50;
    private const string ALLOWED_CHARACTERS_PATTERN = '/^[a-z0-9._-]+$/D';
    private const string ALLOWED_START_CHARACTERS_PATTERN = '/^[a-z0-9]/';
    private const string ALLOWED_END_CHARACTERS_PATTERN = '/[a-z0-9]$/D';
    private const string CONSECUTIVE_SEPARATORS_PATTERN = '/[._\-]{2,}/';

    private function __construct(
        public string $value,
    ) {
    }

    public static function create(string $login): self
    {
        $login = $login
                |> trim(...)
                |> strtolower(...);

        if (strlen($login) < self::MIN_LENGTH
            || strlen($login) > self::MAX_LENGTH
            || 1 !== preg_match(self::ALLOWED_CHARACTERS_PATTERN, $login)
            || 1 !== preg_match(self::ALLOWED_START_CHARACTERS_PATTERN, $login)
            || 1 !== preg_match(self::ALLOWED_END_CHARACTERS_PATTERN, $login)
            || 0 !== preg_match(self::CONSECUTIVE_SEPARATORS_PATTERN, $login)
        ) {
            throw InvalidLoginException::create();
        }

        return new self($login);
    }
}
