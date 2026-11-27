<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account\Event;

use App\Accounts\Domain\Account\AccountId;
use App\SharedKernel\Domain\DomainEvent;
use App\SharedKernel\Domain\Email;

final readonly class AccountRegistered implements DomainEvent
{
    public function __construct(
        public AccountId $accountId,
        public Email $email,
        public \DateTimeImmutable $validUntil,
    ) {
    }
}
