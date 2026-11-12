<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

use App\SharedKernel\Domain\AggregateRoot;

final class Gadget extends AggregateRoot
{
    private function __construct(private string $id, private string $name)
    {
    }

    public static function register(string $id, string $name): self
    {
        self::checkRule(new GadgetNameMustNotBeBlank($name));

        $gadget = new self($id, $name);
        $gadget->recordThat(new GadgetRegistered($id, $name));

        return $gadget;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
