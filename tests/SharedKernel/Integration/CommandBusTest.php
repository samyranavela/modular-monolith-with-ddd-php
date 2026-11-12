<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Integration;

use App\SharedKernel\Application\IdGenerator;
use App\SharedKernel\Domain\AggregateNotFound;
use App\SharedKernel\Domain\BusinessRuleViolation;
use App\Tests\Fixture\Sandbox\Application\FindGadget;
use App\Tests\Fixture\Sandbox\Application\GadgetView;
use App\Tests\Fixture\Sandbox\Application\RegisterGadget;
use App\Tests\Fixture\Sandbox\Application\UnobservedEvent;
use App\Tests\Fixture\Sandbox\Infrastructure\SandboxSchema;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

/**
 * Entry point 2 of the S0b test strategy: send messages on the buses, observe through the buses.
 * The sandbox module (tests/Fixture/Sandbox) is the only module that exists at this stage.
 */
#[CoversNothing]
final class CommandBusTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        SandboxSchema::ensure(self::getContainer()->get(EntityManagerInterface::class));
    }

    public function testCommandPersistsItsAggregateAndTheReactionToItsDomainEventInOneTransaction(): void
    {
        $id = $this->newId();

        $this->commands()->dispatch(new RegisterGadget($id, 'Sprocket'));

        $view = $this->find($id);
        self::assertSame('Sprocket', $view->name);
        // The domain event published by the handler was processed synchronously, in the same transaction.
        self::assertTrue($view->announced);
    }

    public function testFailureAfterTheDomainEventWasPublishedRollsBackTheAggregateAndTheReaction(): void
    {
        $id = $this->newId();

        try {
            // The sandbox reaction to this name flushes, then throws: the SQL was issued before the failure.
            $this->commands()->dispatch(new RegisterGadget($id, 'explode'));
            self::fail('The command should have failed.');
        } catch (HandlerFailedException) {
        }

        $this->assertNotFound($id);
    }

    public function testBusinessRuleViolationCarriesTheStableRuleNameAndLeavesNothing(): void
    {
        $id = $this->newId();

        try {
            $this->commands()->dispatch(new RegisterGadget($id, '  '));
            self::fail('The command should have failed.');
        } catch (HandlerFailedException $e) {
            $violations = array_values($e->getWrappedExceptions(BusinessRuleViolation::class));
            self::assertCount(1, $violations);
            self::assertSame('sandbox.gadget_name_must_not_be_blank', $violations[0]->getRuleName());
        }

        $this->assertNotFound($id);
    }

    public function testEventWithoutHandlerIsNotAnError(): void
    {
        $this->events()->dispatch(new UnobservedEvent());

        $this->addToAssertionCount(1);
    }

    public function testGeneratedIdsAreUuidV7(): void
    {
        $generated = self::getContainer()->get(IdGenerator::class)->generate();

        self::assertInstanceOf(UuidV7::class, Uuid::fromString($generated));
    }

    private function find(string $id): GadgetView
    {
        $envelope = $this->queries()->dispatch(new FindGadget($id));
        $result = $envelope->last(HandledStamp::class)?->getResult();
        self::assertInstanceOf(GadgetView::class, $result);

        return $result;
    }

    private function assertNotFound(string $id): void
    {
        // A new request starts with an empty identity map: read what the database holds, not what the failed command left in memory.
        self::getContainer()->get(EntityManagerInterface::class)->clear();

        try {
            $this->queries()->dispatch(new FindGadget($id));
            self::fail('Nothing should have been persisted.');
        } catch (HandlerFailedException $e) {
            self::assertCount(1, $e->getWrappedExceptions(AggregateNotFound::class));
        }
    }

    private function newId(): string
    {
        return self::getContainer()->get(IdGenerator::class)->generate();
    }

    private function commands(): MessageBusInterface
    {
        return $this->bus('command.bus');
    }

    private function queries(): MessageBusInterface
    {
        return $this->bus('query.bus');
    }

    private function events(): MessageBusInterface
    {
        return $this->bus('event.bus');
    }

    private function bus(string $name): MessageBusInterface
    {
        $bus = self::getContainer()->get($name);
        self::assertInstanceOf(MessageBusInterface::class, $bus);

        return $bus;
    }
}
