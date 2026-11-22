<?php

declare(strict_types=1);

namespace App\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\AccountAlreadyConfirmed;
use App\Accounts\Domain\Account\Exception\RegistrationConfirmationDelayExpired;
use App\SharedKernel\Domain\Email;

class Account
{
    private const string PENDING_VALIDITY = '+48 hours';

    private function __construct(
        public readonly AccountRole $role,
        public private(set) Email $email,
        public private(set) AccountStatus $status,
        public private(set) \DateTimeImmutable $validUntil,
        public private(set) ?\DateTimeImmutable $confirmedAt = null,
        public private(set) ?Login $login = null,
    ) {
    }

    private static function create(AccountRole $role, Email $email, \DateTimeImmutable $createdAt): self
    {
        return new self(
            $role,
            $email,
            AccountStatus::PENDING,
            $createdAt->modify(self::PENDING_VALIDITY),
        );
    }

    public static function register(Email $email, \DateTimeImmutable $registeredAt): self
    {
        return self::create(
            AccountRole::MEMBER,
            $email,
            $registeredAt,
        );
    }

    public static function invite(Email $email, \DateTimeImmutable $invitedAt): self
    {
        return self::create(
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
