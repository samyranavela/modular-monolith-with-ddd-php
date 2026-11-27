<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Application\Registration;

use App\Accounts\Application\Registration\RegisterAccount;
use App\Accounts\Application\Registration\RegisterAccountHandler;
use App\Accounts\Domain\Account\AccountId;
use App\Accounts\Domain\Account\AccountRole;
use App\Accounts\Domain\Account\AccountStatus;
use App\Accounts\Domain\Account\Event\AccountRegistered;
use App\SharedKernel\Domain\Email;
use App\Tests\Unit\SharedKernel\Application\FixedClock;
use App\Tests\Unit\SharedKernel\Application\RecordingEventDispatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class RegisterAccountHandlerTest extends TestCase
{
    private InMemoryAccountRepository $accounts;
    private RecordingEventDispatcher $dispatchedEvents;
    private RegisterAccountHandler $handler;
    private AccountId $accountId;

    protected function setUp(): void
    {
        $this->accountId = new AccountId(Uuid::fromString('0192f1c4-8a3e-7b6d-9c21-4f5a6b7c8d9e'));
        $this->accounts = new InMemoryAccountRepository();
        $this->dispatchedEvents = new RecordingEventDispatcher();
        $this->handler = new RegisterAccountHandler(
            $this->accounts,
            new FixedAccountIdGenerator($this->accountId),
            new FixedClock(new \DateTimeImmutable('2026-01-01 10:00:00')),
            $this->dispatchedEvents,
        );
    }

    #[Test]
    public function a_free_email_address_registers_a_pending_member(): void
    {
        ($this->handler)(new RegisterAccount('email@domain.net'));

        $registered = $this->accounts->all();
        self::assertCount(1, $registered);
        self::assertSame($this->accountId, $registered[0]->id);
        self::assertSame('email@domain.net', $registered[0]->email->value);
        self::assertSame(AccountRole::MEMBER, $registered[0]->role);
        self::assertSame(AccountStatus::PENDING, $registered[0]->status);
        self::assertEquals(new \DateTimeImmutable('2026-01-03 10:00:00'), $registered[0]->validUntil);
    }

    #[Test]
    public function registering_dispatches_that_the_account_was_registered(): void
    {
        ($this->handler)(new RegisterAccount('email@domain.net'));

        self::assertEquals(
            [new AccountRegistered(
                $this->accountId,
                Email::create('email@domain.net'),
                new \DateTimeImmutable('2026-01-03 10:00:00'),
            )],
            $this->dispatchedEvents->dispatched(),
        );
    }
}
