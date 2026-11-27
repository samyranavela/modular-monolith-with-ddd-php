<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidPasswordException;
use App\Accounts\Domain\Account\Exception\InvalidPasswordHashException;
use App\Accounts\Domain\Account\Password;
use App\Accounts\Domain\Account\PasswordHasher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PasswordTest extends TestCase
{
    #[Test]
    public function a_password_matches_its_own_hash(): void
    {
        $hasher = new FakePasswordHasher();
        $password = Password::create(str_repeat('a', 15));

        $hash = $password->hashWith($hasher);

        self::assertTrue($password->matches($hash, $hasher));
    }

    #[Test]
    public function a_password_does_not_match_the_hash_of_another_password(): void
    {
        $hasher = new FakePasswordHasher();
        $hash = Password::create(str_repeat('a', 15))->hashWith($hasher);

        self::assertFalse(Password::create(str_repeat('b', 15))->matches($hash, $hasher));
    }

    #[Test]
    public function a_password_cannot_be_hashed_to_an_empty_hash(): void
    {
        $this->expectException(InvalidPasswordHashException::class);

        $hasher = new class implements PasswordHasher {
            public function hash(string $plain): string
            {
                return '';
            }

            public function verify(string $plain, string $hash): bool
            {
                return false;
            }
        };

        Password::create(str_repeat('a', 15))->hashWith($hasher);
    }

    #[Test]
    #[DataProvider('passwordsWithSpaces')]
    public function a_password_is_not_trimmed(string $password): void
    {
        $this->expectNotToPerformAssertions();

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function passwordsWithSpaces(): iterable
    {
        yield 'start with a space' => [' '.str_repeat('a', 14)];
        yield 'end with a space' => [str_repeat('a', 14).' '];
        yield 'surrounded with spaces' => [' '.str_repeat('a', 13).' '];
    }

    #[Test]
    #[DataProvider('passwordsWithoutCompositions')]
    public function a_password_has_no_composition_rule(string $password): void
    {
        $this->expectNotToPerformAssertions();

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function passwordsWithoutCompositions(): iterable
    {
        yield '15 lowercase letters' => [str_repeat('a', 15)];
        yield '15 uppercase letters' => [str_repeat('B', 15)];
        yield '15 digits' => [str_repeat('1', 15)];
        yield '15 symbols' => [str_repeat('!', 15)];
    }

    #[Test]
    #[DataProvider('passwordsAtTheLengthLimits')]
    public function a_password_accepts_the_length_limits(string $password): void
    {
        $this->expectNotToPerformAssertions();

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function passwordsAtTheLengthLimits(): iterable
    {
        yield 'minimum, 15 characters' => [str_repeat('a', 15)];
        yield '15 emojis' => [str_repeat("\u{1F603}", 15)];
        yield 'maximum, 128 characters' => [str_repeat('a', 128)];
        yield '128 emojis' => [str_repeat("\u{1F603}", 128)];
    }

    #[Test]
    #[DataProvider('tooShortPasswords')]
    public function a_password_cannot_be_shorter_than_fifteen_characters(string $password): void
    {
        $this->expectException(InvalidPasswordException::class);

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function tooShortPasswords(): iterable
    {
        yield '14 characters' => [str_repeat('a', 14)];
        yield '14 emojis' => [str_repeat("\u{1F603}", 14)];
    }

    #[Test]
    #[DataProvider('tooLongPasswords')]
    public function a_password_cannot_exceed_the_length_limits(string $password): void
    {
        $this->expectException(InvalidPasswordException::class);

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function tooLongPasswords(): iterable
    {
        yield '129 characters' => [str_repeat('a', 129)];
        yield '129 emojis' => [str_repeat("\u{1F603}", 129)];
    }

    #[Test]
    #[DataProvider('invalidUtf8Passwords')]
    public function a_password_must_be_valid_utf8(string $password): void
    {
        $this->expectException(InvalidPasswordException::class);

        Password::create($password);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function invalidUtf8Passwords(): iterable
    {
        yield '15 invalid octets' => [str_repeat("\xFF", 15)];
        yield '14 characters ending with an invalid octet' => [str_repeat('a', 14)."\xFF"];
    }
}
