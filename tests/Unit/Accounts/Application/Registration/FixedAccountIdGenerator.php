<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Application\Registration;

use App\Accounts\Application\Registration\AccountIdGenerator;
use App\Accounts\Domain\Account\AccountId;

final readonly class FixedAccountIdGenerator implements AccountIdGenerator
{
    public function __construct(private AccountId $accountId)
    {
    }

    public function next(): AccountId
    {
        return $this->accountId;
    }
}
