<?php

declare(strict_types=1);

namespace App\Accounts\Application\Registration;

use App\Accounts\Domain\Account\AccountId;
use App\SharedKernel\Domain\Email;

interface ConfirmationLinkSender
{
    public function send(AccountId $accountId, Email $email, \DateTimeImmutable $validUntil): void;
}
