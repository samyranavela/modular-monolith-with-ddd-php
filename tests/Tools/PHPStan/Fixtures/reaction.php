<?php

declare(strict_types=1);

// The rule test's input (autoload-dev classmap, for PHPStan's reflection): one violation next to the shapes that pass.

namespace App\Fixture\Reaction {
    use App\SharedKernel\Domain\DomainEvent;

    final readonly class AccountRegistered implements DomainEvent {}

    final readonly class RegisterAccount {}

    final class SendLinkOnAccountRegistered
    {
        public function __invoke(AccountRegistered $event): void {}
    }

    final class SendLink
    {
        public function __invoke(AccountRegistered $event): void {}
    }

    final class RegisterAccountHandler
    {
        public function __invoke(RegisterAccount $command): void {}
    }
}
