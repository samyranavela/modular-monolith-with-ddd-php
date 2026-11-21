<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain\Exception;

final class InvalidEmailException extends \InvalidArgumentException
{
    private function __construct(string $email)
    {
        parent::__construct(sprintf('Invalid email address: %s', $email));
    }

    public static function create(string $email): self
    {
        return new self($email);
    }
}
