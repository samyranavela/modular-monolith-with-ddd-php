<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Application\Registration;

use App\Accounts\Application\AccountRepository;
use App\Accounts\Domain\Account\Account;

final class InMemoryAccountRepository implements AccountRepository
{
    /** @var list<Account> */
    private array $accounts = [];

    public function add(Account $account): void
    {
        $this->accounts[] = $account;
    }

    /**
     * @return list<Account>
     */
    public function all(): array
    {
        return $this->accounts;
    }
}
