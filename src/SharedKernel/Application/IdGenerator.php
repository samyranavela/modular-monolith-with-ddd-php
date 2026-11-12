<?php

declare(strict_types=1);

namespace App\SharedKernel\Application;

/**
 * Generates an identifier before the command is dispatched (a command never returns a value).
 */
interface IdGenerator
{
    public function generate(): string;
}
