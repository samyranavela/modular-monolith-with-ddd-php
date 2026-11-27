<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account\Exception;

final class InvalidPasswordHashException extends \InvalidArgumentException
{
    public static function create(): self
    {
        return new self();
    }
}
