<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain\Account;

use App\Accounts\Domain\Account\AccountId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AccountIdTest extends TestCase
{
    #[Test]
    public function an_account_id_holds_the_uuid_it_is_created_from(): void
    {
        $uuid = Uuid::fromString('0192f1c4-8a3e-7b6d-9c21-4f5a6b7c8d9e');

        self::assertSame($uuid, new AccountId($uuid)->value);
    }

    #[Test]
    public function account_ids_of_the_same_uuid_are_equal(): void
    {
        self::assertEquals(
            new AccountId(Uuid::fromString('0192f1c4-8a3e-7b6d-9c21-4f5a6b7c8d9e')),
            new AccountId(Uuid::fromString('0192f1c4-8a3e-7b6d-9c21-4f5a6b7c8d9e')),
        );
    }
}
