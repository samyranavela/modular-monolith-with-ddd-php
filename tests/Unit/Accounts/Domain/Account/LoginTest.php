<?php

declare(strict_types=1);

namespace App\Tests\Unit\Accounts\Domain\Account;

use App\Accounts\Domain\Account\Exception\InvalidLoginException;
use App\Accounts\Domain\Account\Login;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    #[Test]
    #[TestWith([' bob', 'bob'], 'start with a space')]
    #[TestWith(['bob ', 'bob'], 'end with a space')]
    #[TestWith([' bob ', 'bob'], 'surrounded with spaces')]
    public function a_login_is_trimmed(string $login, string $expected): void
    {
        self::assertSame($expected, Login::create($login)->value);
    }

    #[Test]
    #[TestWith(['Bob', 'bob'], 'start with uppercase')]
    #[TestWith(['bOb', 'bob'], 'uppercase in between')]
    #[TestWith(['BOB', 'bob'], 'all uppercase')]
    public function a_login_is_lowercased(string $login, string $expected): void
    {
        self::assertSame($expected, Login::create($login)->value);
    }

    #[Test]
    public function logins_differing_only_by_case_are_equal(): void
    {
        self::assertEquals(Login::create('bob'), Login::create('Bob'));
    }

    #[Test]
    #[TestWith(['123', '123'], 'all digits')]
    #[TestWith(['000', '000'], 'all zeros')]
    #[TestWith(['1bob', '1bob'], 'start with a digit')]
    #[TestWith(['bob1', 'bob1'], 'end with a digit')]
    #[TestWith(["bob\0", 'bob'], 'end with a NUL character')]
    public function a_login_accepts_letters_and_digits(string $login, string $expected): void
    {
        self::assertSame($expected, Login::create($login)->value);
    }

    #[Test]
    #[TestWith(['a.b.c', 'a.b.c'], 'dots between letters')]
    #[TestWith(['jean-dupont', 'jean-dupont'], 'dash between letters')]
    #[TestWith(['jean_dupont', 'jean_dupont'], 'underscore between letters')]
    public function a_login_accepts_its_allowed_separators(string $login, string $expected): void
    {
        self::assertSame($expected, Login::create($login)->value);
    }

    #[Test]
    #[TestWith([''], 'empty')]
    #[TestWith([' '], 'spaces')]
    #[TestWith(["\t"], 'tabs')]
    #[TestWith(["\n"], 'new lines \n')]
    #[TestWith(["\r"], 'new lines \r')]
    public function a_login_cannot_be_empty(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    #[Test]
    #[TestWith(['john doe'], 'a space in the middle')]
    #[TestWith(['john@doe'], 'an at sign')]
    #[TestWith(["jos\u{00E9}s"], 'an accented letter (precomposed é) in the middle')]
    #[TestWith(["jose\u{0301}s"], 'an accented letter (e followed by a combining accent) in the middle')]
    #[TestWith(["b\u{043E}b"], 'a Cyrillic letter that looks like a Latin o')]
    #[TestWith(["b\u{FF4F}b"], 'a fullwidth Latin letter in the middle')]
    #[TestWith(["b\u{1F603}b"], 'an emoji in the middle')]
    #[TestWith(["bo\nb"], 'a new line in the middle')]
    #[TestWith(["bo\0b"], 'a NUL character in the middle')]
    public function a_login_cannot_contain_characters_outside_the_allowed_set(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    #[Test]
    #[TestWith(['.bob'], 'start with a point')]
    #[TestWith(['-bob'], 'start with a dash')]
    #[TestWith(['_bob'], 'start with an underscore')]
    #[TestWith(['bob.'], 'end with a point')]
    #[TestWith(['bob-'], 'end with a dash')]
    #[TestWith(['bob_'], 'end with an underscore')]
    public function a_login_must_start_and_end_with_a_letter_or_a_digit(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    #[Test]
    #[TestWith(['bo..b'], 'consecutive points')]
    #[TestWith(['bo--b'], 'consecutive dashes')]
    #[TestWith(['bo__b'], 'consecutive underscores')]
    #[TestWith(['bo-.b'], 'consecutive dash and point')]
    #[TestWith(['bo_.b'], 'consecutive underscore and point')]
    public function a_login_cannot_have_consecutive_separators(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    #[Test]
    #[TestWith(['ab'], '2 characters')]
    #[TestWith([' ab'], '2 characters with a space around')]
    #[TestWith(['0'], '1 digit')]
    public function a_login_cannot_be_shorter_than_three_characters(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    #[Test]
    #[DataProvider('loginsAtTheLengthLimits')]
    public function a_login_accepts_the_length_limits(string $login, string $expected): void
    {
        self::assertSame($expected, Login::create($login)->value);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function loginsAtTheLengthLimits(): iterable
    {
        yield 'minimum, 3 characters' => ['bob', 'bob'];
        yield 'maximum, 50 characters' => [str_repeat('a', 50), str_repeat('a', 50)];
        yield 'maximum, with a space around' => [' '.str_repeat('a', 50), str_repeat('a', 50)];
    }

    #[Test]
    #[DataProvider('tooLongLogins')]
    public function a_login_cannot_exceed_fifty_characters(string $login): void
    {
        $this->expectException(InvalidLoginException::class);

        Login::create($login);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function tooLongLogins(): iterable
    {
        yield '51 characters' => [str_repeat('a', 51)];
        yield '51 characters with a space around' => [' '.str_repeat('a', 51)];
    }
}
