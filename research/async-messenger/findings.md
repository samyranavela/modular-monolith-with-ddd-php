# Symfony equivalents of Quartz, outbox, inbox and internal commands

Research ticket: issue #4 (wayfinder:research; map is issue #1). Date: 2026-11-12. No decision is made; options and trade-offs only.

## 0. Method and reliability

- .NET source read directly from `modular-monolith-with-ddd` (read-only); paths relative to `src/`.
- The Symfony project has no `vendor/`, so the target stack was installed in a scratch dir outside the repo and the installed source was read: symfony/messenger 8.1.11, doctrine-messenger 8.1.11, scheduler 8.1.10, lock 8.1.11, framework-bundle 8.1.x, validator 8.1.11, doctrine/orm 3.7.6, dbal 4.5.1, doctrine-bundle 3.3.4, zenstruck/messenger-test 1.15.1 (supports Symfony ^8.0). Official docs read via context7 (/symfony/symfony-docs, newest indexed snapshot 7.4); 8.1-only facts come from vendor source and CHANGELOGs.
- Tags: [src] read in .NET code; [vendor] read in installed Symfony source; [doc] Symfony docs via context7; [inferred] reasoning; [unverified] must be tested.

## 1. How the .NET source does it

Stack: MediatR, Autofac decorators, EF Core, Dapper for polling queries, Quartz.NET, Polly, Newtonsoft, SQL Server with one schema per module. Each module (Administration, Meetings, Payments, Registrations, UserAccess) has its own Autofac container, its own Quartz scheduler and its own OutboxMessages/InboxMessages/InternalCommands tables. Meetings is the reference (`Modules/Meetings/Infrastructure/Configuration/...`).

### 1.1 Domain events, in-memory

- Aggregates call `Entity.AddDomainEvent` (BuildingBlocks/Domain/Entity.cs).
- `UnitOfWork.CommitAsync` (BuildingBlocks/Infrastructure/UnitOfWork.cs) runs `DomainEventsDispatcher.DispatchEventsAsync()` then `SaveChangesAsync()`.
- `DomainEventsAccessor` scans the EF ChangeTracker for `Entity` entries.
- `DispatchEventsAsync`: (1) collect events and optionally resolve an `IDomainEventNotification<TEvent>` wrapper (only for events that leave the module); (2) clear events; (3) `mediator.Publish(domainEvent)` synchronously, in the same scope/DbContext, before SaveChanges; (4) serialise each notification to JSON and add an `OutboxMessage` to the same DbContext.
- `DomainEventsDispatcherNotificationHandlerDecorator` re-dispatches after every notification handler (recursion).
- No explicit transaction: atomicity is the single SaveChanges.
- Name mapping: `BiDictionary<string, Type>` per module in `MeetingsStartup`; `OutboxModule.CheckMappings()` throws at startup if a notification type is unmapped, so persisted names don't depend on FQCNs.

### 1.2 Outbox

- Table OutboxMessages(Id, OccurredOn, Type, Data, ProcessedDate); Id is the domain event id. Written via `OutboxAccessor.Add` (EF), saved with the aggregate.
- `ProcessOutboxJob` (Quartz, `[DisallowConcurrentExecution]`) -> `ProcessOutboxCommand` -> handler: `SELECT ... WHERE ProcessedDate IS NULL ORDER BY OccurredOn` (no LIMIT, no row lock); per message: deserialise by mapped name, `mediator.Publish`, then a separate `UPDATE ProcessedDate`. Handler effects and the flag are not in one transaction, so delivery is at-least-once. An exception aborts the batch and the messages are retried next tick (head-of-line blocking, ordered by OccurredOn).
- Notification handlers either publish an integration event on `IEventsBus` (e.g. MeetingAttendeeAddedPublishEventNotificationHandler) or enqueue an internal command (e.g. MeetingAttendeeAddedNotificationHandler -> SendMeetingAttendeeAddedEmailCommand).

### 1.3 Integration events and inbox

- `InMemoryEventBus` is a static singleton `Dictionary<FullName, List<handler>>`, sequential sync calls; `IEventsBus` is meant to be swappable for a broker. Publishing a type with no subscriber would throw KeyNotFound [inferred from code].
- The receiving module subscribes in `EventsBusStartup` with `IntegrationEventGenericHandler<T>`, whose only job is `INSERT INTO [meetings].[InboxMessages]` using the integration event's Id as PK. A duplicate delivery violates the PK; that is the only dedup.
- `ProcessInboxJob` -> handler: select unprocessed ordered by OccurredOn, resolve type by assembly scan, `mediator.Publish`, then `UPDATE ProcessedDate`. It rethrows, so one poison message blocks all later ones. Handlers (e.g. NewUserRegisteredIntegrationEventHandler) typically enqueue an internal command.

### 1.4 Internal commands and retry

- `CommandsScheduler.EnqueueAsync` inserts a row into InternalCommands(Id, EnqueueDate, Type, Data, ProcessedDate, Error) via Dapper on the scoped connection.
- `ProcessInternalCommandsJob` -> handler: select unprocessed ordered by EnqueueDate; per command a Polly WaitAndRetry with waits 1 s, 2 s, 3 s (blocking the job); the command runs in a new DI scope via `CommandsExecutor` (full pipeline). On final failure: `UPDATE ProcessedDate = now, Error = exception`; parked, never retried automatically.
- On success, `UnitOfWorkCommandHandlerDecorator` sets ProcessedDate on the InternalCommands row in the same SaveChanges as the command's effects (`if (command is InternalCommandBase)`).

### 1.5 Quartz jobs

- Per module, `QuartzStartup` builds a StdSchedulerFactory named after the module (in-memory store, no persistence or clustering). Three jobs per module (outbox, inbox, internal commands), each cron `0/2 * * ? * *` (every 2 s) or, when `internalProcessingPoolingInterval` is passed (tests), a simple repeat trigger.
- Payments adds ExpireSubscriptionsJob and ExpireSubscriptionPaymentsJob (cron `0/59 * * ? * *`, fires at seconds 0 and 59 each minute; looks accidental). `ExpireSubscriptionsCommandHandler` enqueues one internal command per expired subscription (fan-out).
- A job only calls `CommandsExecutor.Execute(new XCommand())` (new scope, `mediator.Send`). `IRecurringCommand` is skipped by `LoggingCommandHandlerDecorator`.

### 1.6 Pipeline and buses

- MediatR is the per-module "bus"; cross-module communication is integration events only.
- Decorators registered in ProcessingModule (later registration wraps earlier [inferred]): Logging -> Validation -> UnitOfWork -> handler. UnitOfWork: handler, mark internal command processed, CommitAsync. Validation (FluentValidation): throws InvalidCommandException. Logging (Serilog): pushes CorrelationId and Command:<id>, logs start/success/failure.
- No transaction decorator. No saga/process manager exists in the source (grep finds none). Multi-step flows are choreographies: notification handler -> internal command -> domain event -> notification -> integration event -> another module's inbox -> internal command (e.g. Registrations -> Meetings/Payments on NewUserRegisteredIntegrationEvent; Payments subscription expiry fan-out).

### 1.7 Source guarantees

| Concern | Behaviour |
|---|---|
| Domain event handlers | sync, same scope, before SaveChanges, same implicit transaction |
| Outbox write | atomic with aggregate |
| Outbox delivery | polling 2 s, ordered, no lock, at-least-once, head-of-line blocking |
| Cross-module transport | static in-memory bus, sync |
| Inbox write | PK on event id |
| Inbox processing | polling, ordered, poison message blocks, at-least-once |
| Internal command effect | atomic with its ProcessedDate |
| Internal command retry | 3x blocking (1/2/3 s), then parked with Error, FIFO |
| Scheduling | Quartz in-process, in-memory, one scheduler per module |
| Concurrency | DisallowConcurrentExecution in one process; no multi-instance safety |

## 2. Verified Symfony 8.1 building blocks

### 2.1 Messenger buses and middleware

- Several buses under `framework.messenger.buses`, each with its own `middleware` list; `default_bus` required when more than one [vendor Configuration.php; doc multiple buses]. Docs example: command bus with validation + doctrine_transaction, query bus with validation, event bus with allow_no_handlers.
- Middleware order [vendor FrameworkExtension.php ~l.2477]: per bus = [add_default_stamps, add_bus_name_stamp, reject_redelivered_message, dispatch_after_current_bus, decode_failed_message, failed_message_processing, deduplicate (if symfony/lock enabled)] + your `middleware:` entries + [send_message, handle_message]; debug prepends `traceable`. So custom middleware (transaction, validation, logging, inbox idempotency) always runs inside dispatch_after_current_bus and outside send/handle.
- `#[AsMessageHandler(bus: '...')]` binds handlers to a bus [doc; not opened in 8.1 source].
- `ValidationMiddleware` needs symfony/validator.
- `DoctrineTransactionMiddleware` (Bridge\Doctrine\Messenger): beginTransaction, handle, `$em->flush()`, commit; rollback on any throwable; on HandlerFailedException it removes all HandledStamps so a retry re-runs every handler [vendor]. Also DoctrinePing/CloseConnection/OpenTransactionLogger middleware, and `DoctrineClearEntityManagerWorkerSubscriber` which clears all managers after each handled or failed message [vendor].
- `DispatchAfterCurrentBusMiddleware` + stamp: messages dispatched from a handler with the stamp are queued and run after the root dispatch (whole stack) finishes; dropped if the root fails; failures of queued ones are collected in DelayedMessageHandlingException [vendor].
- `DeduplicateMiddleware` + `DeduplicateStamp(key, ttl=300.0, onlyDeduplicateInQueue=false)` (since 7.3): at dispatch it acquires a Lock on the key and silently drops the message if held; released after handling (8.1 adds ReleaseDeduplicationLockOnFailureListener when it won't be retried) [vendor, CHANGELOG 7.3/8.1]. TTL-based, store-dependent, not a durable processed-id record.
- Retry: transport-level `retry_strategy` (max_retries 3, delay 1000 ms, multiplier 2, max_delay, jitter, or custom RetryStrategyInterface service) [vendor Configuration.php l.1779-1800; doc]. RecoverableMessageHandlingException forces retry; UnrecoverableMessageHandlingException skips retry [vendor SendFailedMessageForRetryListener]. After retries: `failure_transport` if configured (global or per transport), else discarded [doc]. Retry is non-blocking (re-sent with delay). `messenger:failed:show|retry|remove` manages failed messages.

### 2.2 Doctrine transport on PostgreSQL (doctrine-messenger 8.1.11)

- One table (messenger_messages default; id, body, headers, queue_name, created_at, available_at, delivered_at); queue_name partitions logical queues; table_name and queue_name are DSN options; auto_setup or migrations.
- Send is transactional with your DBAL connection: `send()` -> `executeInsert()` does `beginTransaction()` (nested when the DSN uses the same Doctrine connection, e.g. `doctrine://default`), `INSERT ... RETURNING id`, `SELECT pg_notify(table_name, queue_name)`, `commit()`. If dispatch happens inside the business transaction (e.g. in doctrine_transaction) the row appears on commit and vanishes on rollback: transactional outbox for free [vendor; nesting and NOTIFY-on-commit semantics inferred, unverified for this stack, test it].
- Receive: `get()` runs a short transaction with SELECT ... FOR UPDATE SKIP LOCKED (since 7.1), sets delivered_at, commits; the worker then handles; `ack()` deletes the row; rows with delivered_at older than `redeliver_timeout` (3600 s default) become available again [vendor, doc]. At-least-once; a message running longer than redeliver_timeout is redelivered concurrently (doc: set it above the longest handler; `--keepalive` since 7.3).
- LISTEN/NOTIFY on by default (`use_notify`); 8.1 source `get_notify_timeout` default 60000 (doc snapshot says 0); 8.1 adds PostgreSqlNotifyOnIdleListener [vendor CHANGELOG].
- Delay via DelayStamp = future available_at. Ordering: ORDER BY available_at ASC with SKIP LOCKED; not strict FIFO with several workers; retried messages re-enter later [vendor + inferred].
- No history: acked rows are deleted; only the failure transport keeps errors (ErrorDetailsStamp).

### 2.3 Scheduler (8.1.10)

- `#[AsSchedule('name')]` providers implementing `ScheduleProviderInterface::getSchedule(): Schedule`; `RecurringMessage::every()` (relative date format, DateInterval or int seconds), `::cron()` (dragonmantank/cron-expression, 5-field, minute granularity), `::trigger()`; attributes `#[AsCronTask]`, `#[AsPeriodicTask(frequency: <int seconds>)]` with `schedule:` and `transports:` params. Each schedule is the Messenger transport `scheduler_<name>`, run with `bin/console messenger:consume scheduler_<name>` [doc]. Schedule has before/after/onFailure listeners; events PreRun/PostRun/Failure.
- Multi-worker: `Schedule::lock(LockInterface)` lets one worker generate messages while others stand by; "more workers gives HA, not throughput" [doc + vendor Schedule.php]. `stateful(CacheInterface)` + `processOnlyLastMissedRun(true)` remember last run and skip missed backlog [vendor].
- Lock stores [vendor symfony/lock/Store]: DoctrineDbalPostgreSqlStore (advisory), DoctrineDbalStore (table), PdoStore, PostgreSqlStore, RedisStore, FlockStore, InMemoryStore, CombinedStore, etc.

### 2.4 zenstruck/messenger-test 1.15.1

- Override transports with `test://` under `when@test`; messages are intercepted and queued; `transport('name')->queue()->assertContains(...)`, `process()`/`processOrFail()`, `dispatched()`, `acknowledged()`, `rejected()`, `unblock()`, `throwExceptions()`/`catchExceptions()`, `enableRetries()` (retries disabled by default; DelayStamp needs a clock), failure-transport testing, `bus('name')->dispatched()` for multiple buses, serialization round-trip testing [README].
- Limits [inferred, unverified where noted]: `test://` has none of the Doctrine transport behaviour (shared transaction, SKIP LOCKED, redelivery); atomicity, rollback and redelivery need integration tests on real PostgreSQL; a message sent during a later-rolled-back transaction is still recorded as dispatched [unverified]; scheduler transports are not covered in the README, so test the provider output and handlers directly [unverified].

## 3. Mechanism by mechanism: options and trade-offs

### 3.1 In-memory domain event dispatch

- A. Explicit unit-of-work middleware on the command bus: aggregates record events (trait/interface); a custom middleware after the handler collects events from touched aggregates, clears them, dispatches each on a synchronous event bus (allow_no_handlers: true), loops until none remain, then doctrine_transaction flushes/commits. Closest to the source (same transaction, before commit). Testable with `bus('event.bus')`. Needs a way to find touched aggregates (repository registry or scanning the UoW identity map; which ORM 3.7 API is unverified).
- B. Doctrine ORM `onFlush`/`postFlush` listener collecting events from the UnitOfWork (analogue of ChangeTracker.Entries). No repository coupling, but dispatching in onFlush to handlers that persist more entities needs manual change-set recomputation; postFlush needs a second flush and loses "before commit". Unverified against ORM 3.7 docs (not opened).
- C. `DispatchAfterCurrentBusStamp` on events dispatched from handlers: built-in, but because dispatch_after_current_bus is outside custom middleware, queued events run after the command's transaction commits, in a separate transaction; failures are collected in DelayedMessageHandlingException and they are dropped if the command fails [vendor]. Semantic change vs source (fits notification-like uses).
- Hybrid: A for consistent domain events, C or outbox for after-commit work.
- Domain event vs notification can stay as two classes; the BiDictionary analogue is a custom Messenger SerializerInterface/type-header mapping (default `type` header is the FQCN) [Transport/Serialization not read in depth]; startup check = compiler pass or unit test that every notification is mapped.

### 3.2 Transactional outbox

- A. Doctrine transport as the outbox (`doctrine://<module connection>?queue_name=meetings_outbox`): notification dispatched inside the open transaction, inserted atomically, a worker (`messenger:consume meetings_outbox`) runs handlers after commit. Replaces ProcessOutboxJob, table and polling. Pros: least code, NOTIFY latency, SKIP LOCKED multi-worker, retry + failure transport. Cons: no ProcessedDate history; no ordering across workers; retried messages lose their place; redeliver_timeout duplicates; head-of-line/strict OccurredOn order not reproduced; a handler failure retries the whole message (with several handlers on one message retry re-runs all unless split per handler/transport [unverified]); dispatch outside a transaction is not atomic; DSN must use the same DBAL connection as the module's EM. Per-module separation: one table with per-module queue_name, or table_name per module; schema-qualified table_name (`meetings.messenger_messages`) is unverified.
- B. Custom outbox table (Doctrine entity or DBAL insert) + relay: entity persisted in the same flush (mirrors OutboxAccessor); a console command/scheduled message does `SELECT ... WHERE processed_at IS NULL ORDER BY occurred_on LIMIT n FOR UPDATE SKIP LOCKED`, dispatches on a bus, marks processed. Exact parity (history, order, ProcessedDate; "mark processed" can be atomic with the handler if in one transaction). Cons: more custom code (relay, purge, locking), polling latency, re-implements the Doctrine transport.
- C. Both: audit table (B) via Doctrine listener plus Doctrine transport (A) for delivery. Doubles the write path; only if history is required.
- D. No outbox (dispatch after commit): loses atomicity; not equivalent.

### 3.3 Integration events and inbox (idempotency)

Transport side:

- A. Event dispatched to a per-subscriber Doctrine-transport queue (`meetings_inbox`, `payments_inbox`) via a small bridging handler per subscriber, or the same class routed to several transports in `routing` (a message routed to N senders is sent to all N; unverified by test). The queue is the inbox.
- B. Explicit inbox table (PK = event id, inserted by the bridging handler, relayed by Scheduler/worker): parity with source, adds the relay of 3.2-B.
- C. Synchronous in-process handling on a shared event bus with no inbox: simplest, no isolation (a subscriber failure fails the publisher's outbox processing); not equivalent.

Idempotency side (outbox relay and Doctrine transport are both at-least-once):

1. Durable `processed_messages` table + custom middleware keyed by integration event id (+ handler/module): `INSERT ... ON CONFLICT DO NOTHING` in the same transaction as handler effects (middleware after doctrine_transaction); skip when nothing inserted. Strong, survives restarts, same key as the source. Custom code, needs purge.
2. Unique constraint at the effect (e.g. unique user id for member creation, catch UniqueConstraintViolationException as success). No extra infrastructure when natural keys exist; per-handler, easy to forget.
3. DeduplicateStamp + DeduplicateMiddleware (Lock): built-in, but TTL-based (default 300 s), depends on the lock store (use DoctrineDbalPostgreSqlStore/DoctrineDbalStore, not flock), dedups at dispatch time, designed for in-flight/queued duplicates; does not cover redelivery by redeliver_timeout or crash retry. Better suited to recurring job messages (3.5).

1 and 2 can be combined.

Poison messages: source blocks the whole inbox; Messenger retries then moves to the failure transport and continues. Behaviour change (better availability, weaker ordering).

### 3.4 Internal commands and retry

- A. Messenger transport (`meetings_internal`, Doctrine) + retry_strategy + failure_transport. `ICommandsScheduler::enqueue()` becomes `$bus->dispatch(new Command(...))` routed to that transport, inside the open transaction (atomic with caller, same mechanism as 3.2-A). The worker consumes it through the module command bus, so validation/transaction/logging middleware apply. Retry: `max_retries: 3, delay: 1000, multiplier 1|2` gives 1/1/1 s or 1/2/4 s; the source's linear 1/2/3 s needs a custom RetryStrategyInterface service. The Error column corresponds to the failure transport + ErrorDetailsStamp; parked commands are retried with `messenger:failed:retry`. "Processed atomically" is replaced by transactional handling + ack (crash after commit before ack redelivers, so commands must be idempotent or use 3.3-1). Pros: native, scalable, non-blocking retries, DelayStamp. Cons: no FIFO with >1 worker, no ProcessedDate history, commands must be serializable (the source also needs JSON + type name).
- B. Custom internal_commands table + relay: same columns, ProcessedDate set in the command's own transaction, Error column. Exact semantics, rebuilds Messenger for little gain.
- C. Synchronous commands only: no retry/persistence; not equivalent.
- Retry is transport-level; handler-level policy needs custom middleware or Recoverable/Unrecoverable exceptions. Workers clear EMs after each message and reset services (kernel.reset) [vendor].

### 3.5 Quartz -> Scheduler

| Source job | Symfony candidate |
|---|---|
| ProcessOutbox/Inbox/InternalCommands every 2 s | disappear with 3.2-A/3.3-A/3.4-A (messenger:consume); or with table-based options `RecurringMessage::every(2, new ProcessOutbox())` / `#[AsPeriodicTask(frequency: 2)]` (cron is minute-granular, so sub-minute needs `every`) |
| ExpireSubscriptions, ExpireSubscriptionPayments (cron `0/59 * * ? * *`) | `RecurringMessage::cron('* * * * *', ...)` or `every('1 minute', ...)`; the 59 s quirk is not reproducible with cron and probably unintended |

- One `#[AsSchedule('payments')]` per module; consumer `messenger:consume scheduler_payments` (one long-running process per schedule; Supervisor/systemd [doc]). Deployment change: Quartz ran inside the host process.
- DisallowConcurrentExecution equivalent: the schedule only emits messages; if routed to an async transport two ticks can overlap. Options: handle the scheduled message in the scheduler worker itself (no `transports` routing, naturally sequential), DeduplicateStamp on the recurring message, or a `LockFactory` lock in the handler (symfony/lock, per AGENTS.md rather than a hand-built flag).
- Several schedule workers: `Schedule::lock()` for HA; `stateful()` + `processOnlyLastMissedRun(true)` avoid a burst after downtime (Quartz in-memory has neither; missed-fire behaviour not replicated 1:1 [inferred]).
- Fan-out in ExpireSubscriptionsCommandHandler maps to dispatching internal commands (3.4).
- Tests: the `internalProcessingPoolingInterval` override isn't needed; with zenstruck dispatch the recurring message manually and `transport()->process()`.

### 3.6 Per-module buses

- Per module a command bus, optionally a query bus and an event bus (e.g. `meetings.command.bus`, `meetings.event.bus`), each with its own middleware; one `default_bus` required [doc]. Handlers bound with `#[AsMessageHandler(bus: ...)]`. Autowiring a specific bus via named alias or `#[Target('meetings.command.bus')]` (AGENTS.md convention); exact alias name unverified, check `debug:autowiring`.
- Trade-off: one global bus pair with namespaced messages is simpler but loses per-module middleware (e.g. a per-module entity manager for `doctrine_transaction`, which accepts an EM name argument [vendor reads $entityManagerName]) and handler-set isolation. Per-module buses mirror the per-module container and make allow_no_handlers and routing explicit.
- Cross-module communication stays via integration events only. Per-bus assertions: zenstruck `bus('name')`.

### 3.7 Middleware for transactions, logging, validation

| Source decorator | Symfony candidate | Notes |
|---|---|---|
| UnitOfWork | doctrine_transaction + domain-event middleware (3.1-A) | no "mark internal command processed" if the command transport is used (ack does it) |
| Validation (FluentValidation, InvalidCommandException) | `validation` middleware, `#[Assert\...]` on message classes, throws ValidationFailedException | rules on the message class instead of validator classes; service-dependent rules need a custom constraint/middleware; validates on every pass incl. worker side |
| Logging (Serilog, correlation id, skip recurring) | custom middleware with LoggerInterface; context from a CorrelationId stamp or RequestStack; Monolog processors replace enrichers; skipping recurring = interface check | Messenger already logs sent/received/handled/failed at the worker; avoid duplicate noise |
| Per-command DI scope (CommandsExecutor) | worker resets services (kernel.reset) and clears EMs per message [vendor] | no manual scope needed |

- Middleware is registered by alias/service id under `framework.messenger.buses.<bus>.middleware`; order matters (2.1). Example only: [logging, validation, doctrine_transaction, domain_events, inbox_idempotency].

### 3.8 Process managers

- None in the source (choreography only). It maps directly onto Messenger handlers dispatching messages (DispatchAfterCurrentBusStamp or the outbox for after-commit). Messenger and Scheduler have no saga facility (grep of vendor finds none). If needed later: a Doctrine entity holding process state handled as ordinary messages (needs idempotency, 3.3), or the Symfony Workflow component for the state machine (unverified, not researched).

## 4. Behaviour differences to accept or compensate

1. Ordering: source globally ordered per module; Messenger with SKIP LOCKED not, beyond available_at; retries reorder.
2. Poison message: source blocks the queue; Messenger retries then failure transport and continues.
3. History: ProcessedDate/Error disappear with the transport (ack deletes; only failed ones kept).
4. Domain event handler transaction: depends on 3.1 option (A same tx, C after commit).
5. Retry: blocking Polly 1/2/3 s vs non-blocking delayed redelivery with exponential/custom strategy.
6. Process model: Quartz inside the app vs separate `messenger:consume` processes per transport/schedule.
7. Duplicates: both at-least-once; Doctrine transport adds redeliver_timeout duplicates, so idempotency becomes a first-class requirement rather than a PK side effect.
8. Cron granularity: no sub-minute cron; use `every`.
9. Wire type names: source maps stable names; Messenger serializes FQCN by default, so renames break queued messages unless a mapping is kept.

## 5. Test strategy (zenstruck/messenger-test)

- Functional level: `test://` transports + InteractsWithMessenger; assert the right messages land on the right transport/bus (aggregate action -> notification on outbox transport, internal command on commands transport) and `process()` them. `enableRetries()` + failure_transport tests retry/failure routing; `throwExceptions()` shows handler errors; DelayStamp needs a clock.
- Integration level (real PostgreSQL, real Doctrine transport) is required for: atomicity (rollback removes the outbox row), SKIP LOCKED concurrency, redelivery after redeliver_timeout, NOTIFY, middleware order, durable idempotency. zenstruck cannot cover these because it replaces the transport.
- Multi-bus assertions (`bus('meetings.command.bus')`) check module isolation.

## 6. Open questions before deciding

1. Is strict per-module ordering of outbox/inbox/internal commands required, or is "at least once, roughly ordered" fine (3.2/3.3/3.4 A vs B)?
2. Is ProcessedDate/Error history needed for operations (audit table)?
3. Must domain-event handlers run in the command's transaction (3.1 A vs C)?
4. One DBAL connection/schema per module (as in source) or one shared database; schema-qualified Messenger table_name needs a spike.
5. How many worker processes to run and supervise (modules x queues x schedules)?
6. Lock store for Scheduler lock/deduplication (DB advisory-lock vs Redis).
7. Keep stable message type names (custom serializer/mapping) from day one?
8. Spikes to resolve [unverified] tags: NOTIFY-on-commit with nested transaction; routing one class to several transports; per-handler failure isolation on one message; onFlush vs middleware event collection with ORM 3.7; Messenger table in a PostgreSQL schema; scheduler transport under zenstruck.

## 7. Sources

- .NET (local, read-only): src/BuildingBlocks/Infrastructure/{UnitOfWork.cs, DomainEventsDispatching/*, EventBus/*, Inbox/*, InternalCommands/*}; src/BuildingBlocks/Application/{Outbox/*, Events/*}; src/BuildingBlocks/Domain/Entity.cs; src/Modules/Meetings/Infrastructure/Configuration/{MeetingsStartup.cs, Processing/**, Quartz/QuartzStartup.cs, EventsBus/*, Mediation/*}; src/Modules/Meetings/Infrastructure/Outbox/*; src/Modules/Payments/Infrastructure/Configuration/Quartz/*; src/Modules/Payments/Application/Subscriptions/ExpireSubscriptions/*; src/Database/**/InitializeDatabase.sql.
- Symfony vendor (Packagist, versions in section 0): symfony/messenger (Middleware/*, Stamp/*, EventListener/SendFailedMessageForRetryListener.php, CHANGELOG.md); symfony/framework-bundle (DependencyInjection/FrameworkExtension.php ~l.2470-2530, Configuration.php ~l.1700-1870, Resources/config/messenger.php); symfony/doctrine-messenger (Transport/Connection.php, PostgreSqlConnection.php, CHANGELOG.md); symfony/doctrine-bridge (Messenger/*); symfony/scheduler (Schedule.php, RecurringMessage.php, Attribute/*, CHANGELOG.md); symfony/lock (Store/*); zenstruck/messenger-test (README.md, src/Transport/TestTransport.php).
- Docs via context7 (/symfony/symfony-docs): messenger.rst (buses, Doctrine transport options, retry, failure transport), scheduler.rst (consuming, lock(), scaling note). Canonical URLs: https://symfony.com/doc/current/messenger.html, https://symfony.com/doc/current/messenger/multiple_buses.html, https://symfony.com/doc/current/scheduler.html, https://symfony.com/doc/current/lock.html.
