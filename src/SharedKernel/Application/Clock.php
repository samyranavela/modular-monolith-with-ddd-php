<?php

declare(strict_types=1);

namespace App\SharedKernel\Application;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
