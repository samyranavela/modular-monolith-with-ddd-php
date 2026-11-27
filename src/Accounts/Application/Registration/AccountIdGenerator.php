<?php

declare(strict_types=1);

namespace App\Accounts\Application\Registration;

use App\Accounts\Domain\Account\AccountId;

interface AccountIdGenerator
{
    public function next(): AccountId;
}
