<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Application;

final readonly class RegisterGadget
{
    public function __construct(public string $id, public string $name)
    {
    }
}
