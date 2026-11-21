<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

enum AccountStatus
{
    case PENDING;
    case CONFIRMED;
}
