<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain;

use App\Accounts\Domain\Account\Account;
use App\Accounts\Domain\Account\AccountStatus;
use App\Accounts\Domain\Account\Exception\AccountAlreadyConfirmed;
use App\Accounts\Domain\Account\Exception\RegistrationConfirmationDelayExpired;
use App\SharedKernel\Domain\Email;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    #[Test]
    public function a_newly_registered_account_holds_its_email_address(): void
    {
        $email = 'email@domain.net';
        $account = $this->givenARegisteredAccount($email);

        self::assertSame($email, $account->email->value);
    }

    #[Test]
    public function registering_creates_a_pending_account(): void
    {
        $account = $this->givenARegisteredAccount();

        self::assertSame(AccountStatus::PENDING, $account->status);
    }

    #[Test]
    public function a_pending_account_is_valid_for_48_hours(): void
    {
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $now);

        self::assertEquals(new \DateTimeImmutable('2026-01-03 10:00:00'), $account->validUntil);
    }

    #[Test]
    public function renewing_a_pending_account_restarts_its_48_hours(): void
    {
        $registrationDate = new \DateTimeImmutable('2026-01-01 10:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $registrationDate);
        self::assertEquals(new \DateTimeImmutable('2026-01-03 10:00:00'), $account->validUntil);

        $now = new \DateTimeImmutable('2026-01-02 11:00:00');
        $account->renewRegistration($now);
        self::assertEquals(new \DateTimeImmutable('2026-01-04 11:00:00'), $account->validUntil);
    }

    #[Test]
    #[TestWith([new \DateTimeImmutable('2026-01-03 10:00:01'), new \DateTimeImmutable('2026-01-05 10:00:01')], '1 second after expiration')]
    #[TestWith([new \DateTimeImmutable('2026-01-10 10:00:00'), new \DateTimeImmutable('2026-01-12 10:00:00')], '1 week after expiration')]
    public function renewing_an_expired_pending_account_restarts_its_48_hours(\DateTimeImmutable $renewingDate, \DateTimeImmutable $renewedValidity): void
    {
        $registrationDate = new \DateTimeImmutable('2026-01-01 10:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $registrationDate);
        $account->renewRegistration($renewingDate);

        self::assertEquals($renewedValidity, $account->validUntil);
    }

    #[Test]
    #[TestWith([new \DateTimeImmutable('2026-01-01 10:01:00')], '1 minute after registration')]
    #[TestWith([new \DateTimeImmutable('2026-01-03 09:59:59')], '1 second before expiration')]
    public function confirming_a_pending_account_confirms_it(\DateTimeImmutable $confirmationDate): void
    {
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $now);
        $account->confirm($confirmationDate);

        self::assertSame(AccountStatus::CONFIRMED, $account->status);
        self::assertEquals($confirmationDate, $account->confirmedAt);
    }

    #[Test]
    public function an_account_can_be_confirmed_only_once(): void
    {
        $this->expectException(AccountAlreadyConfirmed::class);

        $now = new \DateTimeImmutable('2026-01-01 10:00:00');
        $confirmationDate = new \DateTimeImmutable('2026-01-03 09:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $now);
        $account->confirm($confirmationDate);
        $account->confirm($confirmationDate);
    }

    #[Test]
    #[TestWith([new \DateTimeImmutable('2026-01-03 10:00:00')], 'exactly at expiration')]
    #[TestWith([new \DateTimeImmutable('2026-01-03 11:00:00')], 'after expiration')]
    public function an_account_can_be_confirmed_only_before_it_expires(\DateTimeImmutable $confirmationDate): void
    {
        $this->expectException(RegistrationConfirmationDelayExpired::class);

        $now = new \DateTimeImmutable('2026-01-01 10:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $now);
        $account->confirm($confirmationDate);
    }

    #[Test]
    public function a_confirmed_account_cannot_be_renewed(): void
    {
        $this->expectException(AccountAlreadyConfirmed::class);

        $registrationDate = new \DateTimeImmutable('2026-01-01 10:00:00');
        $confirmationDate = new \DateTimeImmutable('2026-01-03 09:00:00');
        $account = $this->givenARegisteredAccount(registrationDate: $registrationDate);
        $account->confirm($confirmationDate);

        $now = new \DateTimeImmutable('2026-01-04 09:00:00');
        $account->renewRegistration($now);
    }

    private function givenARegisteredAccount(
        string $registrationEmail = 'email@domain.net',
        \DateTimeImmutable $registrationDate = new \DateTimeImmutable(),
    ): Account {
        return Account::register(Email::create($registrationEmail), $registrationDate);
    }
}
