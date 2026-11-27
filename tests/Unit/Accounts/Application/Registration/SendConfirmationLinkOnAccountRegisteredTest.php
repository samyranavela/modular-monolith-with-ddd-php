<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Application\Registration;

use App\Accounts\Application\Registration\SendConfirmationLinkOnAccountRegistered;
use App\Accounts\Domain\Account\AccountId;
use App\Accounts\Domain\Account\Event\AccountRegistered;
use App\SharedKernel\Domain\Email;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class SendConfirmationLinkOnAccountRegisteredTest extends TestCase
{
    #[Test]
    public function a_registered_account_gets_its_confirmation_link(): void
    {
        $accountId = new AccountId(Uuid::fromString('0192f1c4-8a3e-7b6d-9c21-4f5a6b7c8d9e'));
        $email = Email::create('email@domain.net');
        $validUntil = new \DateTimeImmutable('2026-01-03 10:00:00');
        $links = new RecordingConfirmationLinkSender();

        new SendConfirmationLinkOnAccountRegistered($links)(new AccountRegistered($accountId, $email, $validUntil));

        self::assertEquals([[$accountId, $email, $validUntil]], $links->sent());
    }
}
