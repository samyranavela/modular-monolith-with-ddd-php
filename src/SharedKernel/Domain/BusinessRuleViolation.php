<?php

declare(strict_types=1);

namespace App\SharedKernel\Domain;

final class BusinessRuleViolation extends \DomainException
{
    public function __construct(private readonly BusinessRule $rule)
    {
        parent::__construct($rule->message());
    }

    public function getRuleName(): string
    {
        return $this->rule->name();
    }
}
