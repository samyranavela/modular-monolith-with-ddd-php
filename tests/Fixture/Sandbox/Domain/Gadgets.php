<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Sandbox\Domain;

interface Gadgets
{
    public function add(Gadget $gadget): void;

    /**
     * @throws \App\SharedKernel\Domain\AggregateNotFound
     */
    public function get(string $id): Gadget;
}
