<?php

declare(strict_types=1);

namespace App\Tools\PHPStan;

use App\SharedKernel\Domain\DomainEvent;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * A class that reacts to a domain event, through __invoke(<an event>), is named after its reaction and the event:
 * <Reaction>On<Event>, so that it cannot be read as a command.
 *
 * @implements Rule<InClassNode>
 */
final readonly class DomainEventReactionRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if (!$class->isClass() || !$class->hasNativeMethod('__invoke')) {
            return [];
        }

        $parameters = $class->getNativeMethod('__invoke')->getOnlyVariant()->getParameters();
        if ([] === $parameters) {
            return [];
        }

        $eventClasses = $parameters[0]->getType()->getObjectClassNames();
        if (1 !== \count($eventClasses) || !new ObjectType(DomainEvent::class)->isSuperTypeOf($parameters[0]->getType())->yes()) {
            return [];
        }

        $event = $this->shortName($eventClasses[0]);
        if (str_ends_with($class->getName(), 'On'.$event)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'The class %s reacts to the domain event %s: its name must end with On%s.',
                $class->getName(),
                $event,
                $event,
            ))
                ->identifier('modulith.reactionName')
                ->build(),
        ];
    }

    private function shortName(string $className): string
    {
        $position = strrpos($className, '\\');

        return false === $position ? $className : substr($className, $position + 1);
    }
}
