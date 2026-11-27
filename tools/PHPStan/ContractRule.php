<?php

declare(strict_types=1);

namespace App\Tools\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A class of a module's Contract (App\<Module>\Contract\) is a public message: final readonly.
 *
 * @implements Rule<InClassNode>
 */
final readonly class ContractRule implements Rule
{
    private const string CONTRACT_NAMESPACE = '/^App\\\\[^\\\\]+\\\\Contract\\\\/';

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if (
            !$class->isClass()
            || 1 !== preg_match(self::CONTRACT_NAMESPACE, $class->getName())
            || ($class->isFinal() && $class->isReadOnly())
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'The Contract class %s must be final readonly: it is a message other modules read, never extended or changed.',
                $class->getName(),
            ))
                ->identifier('modulith.contractFinalReadonly')
                ->build(),
        ];
    }
}
