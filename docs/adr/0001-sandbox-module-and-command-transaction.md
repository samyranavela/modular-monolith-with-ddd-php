# Sandbox test module, command transaction and test isolation

Status: accepted (slice S0b-1)

## Context

The kernel (buses, transaction, domain events) needs proof before any real module exists. The proof needs an aggregate persisted with Doctrine, which brings tables that must not ship.

## Decision

- **Sandbox module under `tests/Fixture/Sandbox`**, namespace `App\Tests\Fixture\Sandbox`, with the module layers (`Domain`, `Application`, `Infrastructure`). Its services are loaded and its XML mapping registered only in `when@test`, so the production container never sees it. Deptrac analyses `src` only: the sandbox is outside the production rules, and is the reference example for module slices.
- **Transaction**: `doctrine_transaction` middleware on `command.bus` (flush, commit, rollback on exception). Handlers do not flush. Domain events are dispatched on `event.bus` by the handler, so their reactions share the transaction. An exception in a reaction rolls back the aggregate too.
- **Sandbox tables** are not migrated. `sandbox:schema:create` (test environment only) creates them through `SchemaTool`; `composer schema` runs it between the migrations and `doctrine:schema:validate`, which therefore stays strict for the real mapping. Tests call the same code first, so `make test` works on its own.
- **Test isolation**: DAMA is enabled (bundle in `test`, PHPUnit extension). Each test runs in a rolled-back transaction; the command transaction nests as a savepoint, and a failing command rolls back to it (proved by `CommandBusTest`). The sandbox tables are created before the tests, so DDL never runs inside the DAMA transaction.

## Consequences

- A rollback test must issue its SQL before failing (flush in the reaction) and clear the identity map before reading back, otherwise it observes the unit of work instead of the database.
- Later slices that need other test-only tables follow the same pattern: map in `when@test`, create in `sandbox:schema:create` (or a sibling command), never in a migration.
