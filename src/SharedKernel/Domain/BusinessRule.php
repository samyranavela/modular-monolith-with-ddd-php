<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

interface BusinessRule
{
    public function isBroken(): bool;

    /**
     * Stable name, part of the HTTP contract (e.g. "registrations.login_must_be_unique"): never rename it.
     */
    public function name(): string;

    public function message(): string;
}
