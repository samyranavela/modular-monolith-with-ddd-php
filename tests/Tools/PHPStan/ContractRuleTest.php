<?php

declare(strict_types=1);

namespace App\Tests\Tools\PHPStan;

use App\Tools\PHPStan\ContractRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @extends RuleTestCase<ContractRule>
 */
final class ContractRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ContractRule();
    }

    #[Test]
    public function a_contract_class_is_final_readonly(): void
    {
        $this->analyse([__DIR__.'/Fixtures/contract.php'], [
            [
                'The Contract class App\Fixture\Contract\Mutable must be final readonly (CODING_STANDARDS MOD-2).',
                12,
            ],
            [
                'The Contract class App\Fixture\Contract\Extensible must be final readonly (CODING_STANDARDS MOD-2).',
                14,
            ],
        ]);
    }
}
