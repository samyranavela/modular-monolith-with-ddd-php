<?php

declare(strict_types=1);

namespace App\Accounts\Application\Registration;

use App\Accounts\Domain\Account\Event\AccountRegistered;

final readonly class SendConfirmationLinkOnAccountRegistered
{
    public function __construct(private ConfirmationLinkSender $links)
    {
    }

    public function __invoke(AccountRegistered $event): void
    {
        $this->links->send($event->accountId, $event->email, $event->validUntil);
    }
}
