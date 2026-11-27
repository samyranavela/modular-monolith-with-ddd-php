<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Application\Registration;

use App\Accounts\Application\Registration\ConfirmationLinkSender;
use App\Accounts\Domain\Account\AccountId;
use App\SharedKernel\Domain\Email;

final class RecordingConfirmationLinkSender implements ConfirmationLinkSender
{
    /** @var list<array{AccountId, Email, \DateTimeImmutable}> */
    private array $sent = [];

    public function send(AccountId $accountId, Email $email, \DateTimeImmutable $validUntil): void
    {
        $this->sent[] = [$accountId, $email, $validUntil];
    }

    /**
     * @return list<array{AccountId, Email, \DateTimeImmutable}>
     */
    public function sent(): array
    {
        return $this->sent;
    }
}
