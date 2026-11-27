<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

use App\SharedKernel\Domain\Exception\InvalidEmailException;

final readonly class Email
{
    private function __construct(public string $value)
    {
    }

    public static function create(string $email): self
    {
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::create($email);
        }

        return new self($email);
    }
}
