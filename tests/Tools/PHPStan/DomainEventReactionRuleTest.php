<?php

declare(strict_types=1);

namespace App\Tests\Tools\PHPStan;

use App\Tools\PHPStan\DomainEventReactionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @extends RuleTestCase<DomainEventReactionRule>
 */
final class DomainEventReactionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DomainEventReactionRule();
    }

    #[Test]
    public function a_reaction_to_a_domain_event_is_named_on_the_event(): void
    {
        $this->analyse([__DIR__.'/Fixtures/reaction.php'], [
            [
                'The class App\Fixture\Reaction\SendLink reacts to the domain event AccountRegistered: its name must end with OnAccountRegistered.',
                19,
            ],
        ]);
    }
}
