<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Event\AccountRegistered;
use App\Accounts\Domain\Account\Exception\AccountAlreadyConfirmed;
use App\Accounts\Domain\Account\Exception\RegistrationConfirmationDelayExpired;
use App\SharedKernel\Domain\DomainEvent;
use App\SharedKernel\Domain\Email;

class Account
{
    private const string PENDING_VALIDITY = '+48 hours';

    /** @var list<DomainEvent> */
    private array $events = [];

    private function __construct(
        public readonly AccountId $id,
        public readonly AccountRole $role,
        public private(set) Email $email,
        public private(set) AccountStatus $status,
        public private(set) \DateTimeImmutable $validUntil,
        public private(set) ?\DateTimeImmutable $confirmedAt = null,
        public private(set) ?Login $login = null,
    ) {
    }

    private static function create(
        AccountId $id,
        AccountRole $role,
        Email $email,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $role,
            $email,
            AccountStatus::PENDING,
            $createdAt->modify(self::PENDING_VALIDITY),
        );
    }

    public static function register(AccountId $id, Email $email, \DateTimeImmutable $registeredAt): self
    {
        $account = self::create(
            $id,
            AccountRole::MEMBER,
            $email,
            $registeredAt,
        );
        $account->events[] = new AccountRegistered($id, $email, $account->validUntil);

        return $account;
    }

    /**
     * @return list<DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public static function invite(AccountId $id, Email $email, \DateTimeImmutable $invitedAt): self
    {
        return self::create(
            $id,
            AccountRole::ADMINISTRATOR,
            $email,
            $invitedAt,
        );
    }

    public function confirm(Login $login, \DateTimeImmutable $confirmedAt): void
    {
        if ($confirmedAt >= $this->validUntil) {
            throw RegistrationConfirmationDelayExpired::create();
        }
        if (AccountStatus::CONFIRMED === $this->status) {
            throw AccountAlreadyConfirmed::create();
        }

        $this->status = AccountStatus::CONFIRMED;
        $this->confirmedAt = $confirmedAt;
        $this->login = $login;
    }

    public function renewRegistration(\DateTimeImmutable $renewedAt): void
    {
        if (AccountStatus::CONFIRMED === $this->status) {
            throw AccountAlreadyConfirmed::create();
        }

        $this->validUntil = $renewedAt->modify(self::PENDING_VALIDITY);
    }
}
