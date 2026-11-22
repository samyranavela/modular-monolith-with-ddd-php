<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

enum AccountRole
{
    case MEMBER;
    case ADMINISTRATOR;
}
