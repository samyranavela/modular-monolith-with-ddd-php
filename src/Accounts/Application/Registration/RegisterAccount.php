<?php

declare(strict_types=1);

namespace App\Accounts\Application\Registration;

final readonly class RegisterAccount
{
    public function __construct(public string $email)
    {
    }
}
