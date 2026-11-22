<?php

declare(strict_types=1);

namespace App\Tests\Unit\SharedKernel\Domain;

use App\SharedKernel\Domain\Email;
use App\SharedKernel\Domain\Exception\InvalidEmailException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function cannot_create_an_invalid_email(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessageIs('Invalid email address: dummy.email.address');

        Email::create('dummy.email.address');
    }
}
