<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account\Exception;

final class RegistrationConfirmationDelayExpired extends \DomainException
{
    private function __construct()
    {
        parent::__construct();
    }

    public static function create(): self
    {
        return new self();
    }
}
