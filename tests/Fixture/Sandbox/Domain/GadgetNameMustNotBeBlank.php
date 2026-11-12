<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

use App\SharedKernel\Domain\BusinessRule;

final readonly class GadgetNameMustNotBeBlank implements BusinessRule
{
    public function __construct(private string $name)
    {
    }

    public function isBroken(): bool
    {
        return '' === trim($this->name);
    }

    public function name(): string
    {
        return 'sandbox.gadget_name_must_not_be_blank';
    }

    public function message(): string
    {
        return 'A gadget needs a name.';
    }
}
