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
        public private(set) Email $email,
        public private(set) AccountStatus $status,
        public private(set) \DateTimeImmutable $validUntil,
        public private(set) ?\DateTimeImmutable $confirmedAt = null,
    ) {
    }

    public static function register(Email $email, \DateTimeImmutable $registeredAt): self
    {
        return new self(
            $email,
            AccountStatus::PENDING,
            $registeredAt->modify(self::PENDING_VALIDITY),
        );
    }

    public function confirm(\DateTimeImmutable $confirmedAt): void
    {
        if ($confirmedAt > $this->validUntil) {
            throw RegistrationConfirmationDelayExpired::create();
        }
        if (AccountStatus::CONFIRMED === $this->status) {
            throw AccountAlreadyConfirmed::create();
        }

        $this->status = AccountStatus::CONFIRMED;
        $this->confirmedAt = $confirmedAt;
    }

    public function renewRegistration(\DateTimeImmutable $renewedAt): void
    {
        if (AccountStatus::CONFIRMED === $this->status) {
            throw AccountAlreadyConfirmed::create();
        }

        $this->validUntil = $renewedAt->modify(self::PENDING_VALIDITY);
    }
}
