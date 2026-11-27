<?php

declare(strict_types=1);

namespace App\Accounts\Application;

use App\Accounts\Domain\Account\Account;

interface AccountRepository
{
    public function add(Account $account): void;
}
