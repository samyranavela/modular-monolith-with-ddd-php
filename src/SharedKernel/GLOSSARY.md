# SharedKernel

The technical and domain building blocks every module reuses. It owns no business concept: its words describe how modules are built, not what they do.

## Language

**Aggregate Root**:
The consistency boundary of a module. It records the domain events its operations raise; it knows nothing about Doctrine or Messenger.
_Avoid_: Entity (for the root), model

**Domain Event**:
A fact raised by an aggregate, named in the past tense. Published explicitly by the command handler after saving, handled synchronously in the transaction of the command; never serialised.
_Avoid_: Event (alone), notification

**Business Rule**:
An invariant checked by the domain. Carries a stable name, which is part of the HTTP contract.
_Avoid_: Validation (that is the Ui layer's input check)

**Business Rule Violation**:
The exception thrown when a business rule is broken. It exposes the stable rule name.

**Aggregate Not Found**:
The exception thrown when an aggregate does not exist (404 at the API).

**Command Bus** / **Query Bus** / **Event Bus**:
The three global buses; there is no bus per module. A command runs in one transaction (flush and commit, rollback on exception) and returns nothing. A query returns a view, never an aggregate. The event bus accepts messages without handler.
_Avoid_: Message bus (alone)

**Domain Event Publisher**:
The application port a command handler uses to publish the domain events of a saved aggregate onto the event bus.

**Id Generator**:
Produces identifiers (UUID v7) before a command is dispatched, so the command carries the id and returns nothing.

**Sandbox**:
A test-only module (`tests/Fixture/Sandbox`) that exercises the kernel end to end and serves as the reference example for module slices. Never shipped.
