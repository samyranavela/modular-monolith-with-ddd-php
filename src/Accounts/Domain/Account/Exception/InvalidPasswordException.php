<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account\Exception;

final class InvalidPasswordException extends \InvalidArgumentException
{
    public static function create(): self
    {
        return new self();
    }
}
