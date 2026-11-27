<?php

declare(strict_types=1);

namespace App\Accounts\Application\Registration;

use App\Accounts\Application\AccountRepository;
use App\Accounts\Domain\Account\Account;
use App\SharedKernel\Application\Clock;
use App\SharedKernel\Application\EventDispatcher;
use App\SharedKernel\Domain\Email;

final readonly class RegisterAccountHandler
{
    public function __construct(
        private AccountRepository $accounts,
        private AccountIdGenerator $accountIds,
        private Clock $clock,
        private EventDispatcher $events,
    ) {
    }

    public function __invoke(RegisterAccount $command): void
    {
        $account = Account::register(
            $this->accountIds->next(),
            Email::create($command->email),
            $this->clock->now(),
        );

        $this->accounts->add($account);
        $this->events->dispatch(...$account->releaseEvents());
    }
}
