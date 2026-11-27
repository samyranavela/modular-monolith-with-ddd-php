<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use Symfony\Component\Uid\Uuid;

final readonly class AccountId
{
    public function __construct(
        public Uuid $value,
    ) {
    }
}
