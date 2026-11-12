# Source inventory per module (.NET "My Meetings" modular monolith)

Research ticket: samyranavela/modular-monolith-with-ddd-php#2 (wayfinder map: #1). Question: for each of the 5 modules of the .NET project, list commands, queries, domain events, integration events, REST endpoints (route, verb, permission), aggregates, tables/schema, process managers, Quartz jobs and existing test scenarios.

This is an inventory only: it records what the source contains, with file paths. It makes no design or porting decision. Observations that look surprising are recorded as "observed" with their location and left unjudged.

## Source and method

- Source: `/home/sranavela/workspace/poc/modular-monolith-with-ddd` (read-only), .NET 8 (`global.json` SDK 8.0.0, `Directory.Build.props` `net8.0`). All paths in these files are relative to that repository root unless they start with `research/`.
- Primary sources only: the C# files, SQL scripts and project files listed in each catalogue. Nothing was taken from the project's README or blog posts.
- Method: file-tree enumeration per module, reading of every command/query/event/notification/controller/Quartz startup file, structured extraction of test method names from NUnit `[Test]` / `[TestCase]` attributes, and cross-checks by search (for example, which commands/queries are instantiated in `src/API/CompanyName.MyMeetings.API/Modules/`).

## Catalogues

| Module | File | Commands (public / internal) | Queries | Domain events | Integration events (pub / consumed) | REST endpoints | Aggregates | Tables (excl. messaging) |
|---|---|---|---|---|---|---|---|---|
| Administration | [administration.md](administration.md) | 3 (1 / 2) | 3 | 4 | 1 / 2 | 2 | 2 | 2 |
| Meetings | [meetings.md](meetings.md) | 31 (23 / 8) | 13 | 32 | 2 (+1 unused) / 4 | 32 | 8 | 13 |
| Payments | [payments.md](payments.md) | 21 (13 / 8) | 7 | 20 | 2 / 2 (+1 declared) | 12 | 7 (event sourced) | 5 projections + event store |
| Registrations | [registrations.md](registrations.md) | 3 (2 / 1) | 1 | 3 | 1 / 0 | 2 | 1 | 1 |
| UserAccess | [useraccess.md](useraccess.md) | 3 (3 / 0) | 5 | 1 | 0 / 0 (+1 declared) | 3 (+ `/connect/token`) | 1 | 4 (+ `app.Emails`) |

Each module file has the same sections: aggregates, commands, queries, domain events, integration events, event flows, REST endpoints, database, Quartz jobs, process managers, existing tests.

Terminology used in the files: "public command" = `CommandBase`/`CommandBase<T>`, callable through the module interface (`IMeetingsModule`, `IPaymentsModule`, ...); "internal command" = `InternalCommandBase`, persisted in `<schema>.InternalCommands` and executed by `ProcessInternalCommandsJob`; "notification" = `DomainNotificationBase<TDomainEvent>` wrapper that is serialized into the outbox.

## Cross-cutting architecture facts (shared by all modules)

- Solution: `src/CompanyName.MyMeetings.sln`; per module `Application`, `Domain`, `Infrastructure`, `IntegrationEvents`, `Tests`; shared `src/BuildingBlocks/{Application,Domain,Infrastructure,Tests}`; host `src/API/CompanyName.MyMeetings.API`; database `src/Database`; cross-module tests `src/Tests/{ArchTests,IntegrationTests,SUT}`.
- Package versions (`src/Directory.Packages.props`): MediatR 12.2.0, Autofac 7.1.0, Dapper 2.1.24, EF Core 8.0.0 (SqlServer), FluentValidation 11.8.1, Quartz 3.8.0, Newtonsoft.Json 13.0.3, Polly 8.2.0, Serilog.AspNetCore 8.0.0, SqlStreamStore.MsSql 1.1.3, dbup-sqlserver 5.0.37, Hellang.Middleware.ProblemDetails 6.5.1, Swashbuckle 6.5.0, NUnit 4.0.1, NSubstitute 5.1.0, FluentAssertions 6.12.0, NetArchTest.Rules 1.3.2. IdentityServer4 is used by UserAccess (`Infrastructure/Configuration/Identity/IdentityConfiguration.cs`).
- Messaging between modules: in-memory event bus (`BuildingBlocks/Infrastructure/EventBus/InMemoryEventBus.cs`, `InMemoryEventBusClient.cs`) wrapped by per-module Inbox / Outbox tables and processed by Quartz jobs. Each module registers its own subscriptions in `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs` through `IntegrationEventGenericHandler<T>`, which writes to the module's inbox.
- Per-module internal commands: `ICommandsScheduler.EnqueueAsync` writes to `<schema>.InternalCommands`; `ProcessInternalCommandsJob` executes them (`Infrastructure/Configuration/Processing/InternalCommands/`).
- Command pipeline decorators per module (`Infrastructure/Configuration/Processing/`): logging, validation (FluentValidation), unit of work (`BuildingBlocks/Infrastructure/DomainEventsDispatching/UnitOfWorkCommandHandlerDecorator.cs`) which dispatches domain events after the handler; domain events go to MediatR in-process, and events that have a registered notification are also serialized to the outbox (`DomainEventsDispatcher.cs`).
- Read side: Dapper over SQL views / tables (`ISqlConnectionFactory`); write side: EF Core for all modules except Payments (event sourcing).
- Module startup (`API/.../Startup.cs`): `MeetingsStartup`, `AdministrationStartup`, `UserAccessStartup`, `PaymentsStartup`, `RegistrationsStartup` initialize in `InitializeModules`; each starts its Quartz scheduler.
- API cross-cutting (`API/.../Configuration/`): `[HasPermission]` / `[NoPermissionRequired]` attributes with `AuthorizationChecker.CheckAllEndpoints()` at startup, `HasPermissionAuthorizationHandler` (reads `GetUserPermissionsQuery` from UserAccess), `CorrelationMiddleware`, `ExecutionContextAccessor`, problem details for `InvalidCommandException` and `BusinessRuleValidationException`, Swagger, CORS allow-all, `app.UseHttpsRedirection()`. Request examples: `API/RequestExamples/*.http`.
- BuildingBlocks inventory (file level): Application (`ISqlConnectionFactory`, `IExecutionContextAccessor`, `InvalidCommandException`, outbox/email/paging abstractions, `DomainNotificationBase`), Domain (`Entity`, `ValueObject`, `TypedIdValueBase`, `IBusinessRule`, `BusinessRuleValidationException`, `DomainEventBase`, `IAggregateRoot`), Infrastructure (domain events dispatching, event bus, inbox/outbox message types, internal commands mapper, emails `EmailSender` writing to `app.Emails`, `UnitOfWork`, serialization helpers, strongly typed id converters). Test: `BuildingBlocks/Tests/Application.UnitTests/Queries/PagedQueryHelperTests.cs` (`PagedQueryHelper_GetPageData_Test`, `[TestCase]`).

## Database overview (SQL Server)

- Schemas (`Structure/Security/Schemas.sql`): `app`, `administration`, `meetings`, `users`, `registrations`, `payments`. Note the UserAccess module's schema is named `users`.
- Shared tables in `app`: `app.Emails` (mock mailbox), `app.MigrationsJournal` (DbUp journal).
- Every module schema has `InboxMessages`, `OutboxMessages`, `InternalCommands` (columns in [administration.md](administration.md)); Payments additionally has the event store.
- Project: `src/Database/CompanyName.MyMeetings.Database/` (SSDT `Structure/`, DbUp `Scripts/Migrations/1_0_0_0/0001..0014_*.sql`, `Scripts/Seeds/0001_SeedCountries.sql`, `Scripts/SeedDatabase.sql`, `Scripts/ClearDatabase.sql`), migrator `src/Database/DatabaseMigrator/` (`Program.cs`, DbUp with Serilog journal), docker files under `src/Database/`.
- Cross-module foreign keys: none observed; the only FKs are inside `meetings` (`MeetingCommentingConfigurations`, `MeetingMemberCommentLikes`) and inside `payments` (`Messages` -> `Streams`).

## Cross-module integration event matrix

Defined in each module's `IntegrationEvents` project; subscriptions in each module's `EventsBusStartup.cs`.

| Event | Defined in | Published by | Consumed by (handler -> effect) |
|---|---|---|---|
| `NewUserRegisteredIntegrationEvent` | Registrations | Registrations (`NewUserRegisteredPublishEventHandler`) | Meetings (`CreateMemberCommand`), Administration (`CreateMemberCommand`), Payments (`CreatePayerCommand`) |
| `MeetingGroupProposedIntegrationEvent` | Meetings | Meetings (`MeetingGroupProposedNotificationHandler`) | Administration (`RequestMeetingGroupProposalVerificationCommand`) |
| `MeetingGroupProposalAcceptedIntegrationEvent` | Administration | Administration (`MeetingGroupProposalAcceptedNotificationHandler`) | Meetings (`AcceptMeetingGroupProposalCommand`); Payments subscribes but has no handler |
| `MeetingAttendeeAddedIntegrationEvent` | Meetings | Meetings (`MeetingAttendeeAddedPublishEventNotificationHandler`) | Payments (`CreateMeetingFeeCommand`) |
| `MeetingFeePaidIntegrationEvent` | Payments | Payments (`MeetingFeePaidNotificationHandler`) | Meetings (`MarkMeetingAttendeeFeeAsPayedCommand`) |
| `SubscriptionExpirationDateChangedIntegrationEvent` | Payments | Payments (`SubscriptionCreatedNotificationHandler`, `SubscriptionRenewedNotificationHandler`) | Meetings (`ChangeSubscriptionExpirationDateForMemberCommand`) |
| `MemberCreatedIntegrationEvent` | Meetings | nobody | UserAccess subscribes, no handler (Registrations: commented-out subscription) |

One synchronous cross-module call exists outside the event bus: Registrations -> UserAccess (`UserAccessGateway` -> `IUserAccessModule.ExecuteCommandAsync(CreateUserCommand)`), and the API's authorization handler calls UserAccess queries on every request.

Compile-time module isolation is checked by `Tests/ArchTests/Modules/ModuleTests.cs` and `Tests/ArchTests/Api/ApiTests.cs` (see below).

## REST surface summary

Base routes (all under controllers in `API/CompanyName.MyMeetings.API/Modules/`):
`/api/administration/meetingGroupProposals`, `/api/meetings/{countries, meetingGroupProposals, meetingGroups, meetings, meetingComments}` (plus `/api/meetings/meetings/{id}/configuration/commenting/*`), `/api/payments/{priceListItems, payers, subscriptions, subscriptionPayments, subscriptionRenewals, meetingFeePayments}`, `/api/userAccess/{authenticatedUser, emails}`, `/userAccess/userRegistrations` (no `api/` prefix), `POST /connect/token` (IdentityServer).

Permissions: three constant classes (`AdministrationPermissions` 1 code, `MeetingsPermissions` 30, `PaymentsPermissions` 9) and 40 distinct `[HasPermission(...)]` references in the controllers; 5 endpoints are `[NoPermissionRequired]` (`GET authenticatedUser`, `GET authenticatedUser/permissions`, `GET emails`, `POST userRegistrations`, `PATCH userRegistrations/{id}/confirm`). Exact per-endpoint permissions are in each module's REST table. Roles: `Member`, `Administrator` (seeded in `users.RolesToPermissions`, see [useraccess.md](useraccess.md)).

## Quartz jobs overview

| Module | Jobs | Schedule |
|---|---|---|
| Administration, Meetings, Registrations, UserAccess | `ProcessOutboxJob`, `ProcessInboxJob`, `ProcessInternalCommandsJob` | cron `0/2 * * ? * *`, or `RepeatForever` with a fixed millisecond interval when `internalProcessingPoolingInterval` is supplied (used by integration tests) |
| Payments | the three above plus `ExpireSubscriptionPaymentsJob`, `ExpireSubscriptionsJob` | latter two: cron `0/59 * * ? * *` |

Each module creates its own scheduler (`quartz.scheduler.instanceName`). Jobs live in each module's `Infrastructure/Configuration/Quartz/` and `.../Processing/{Inbox,Outbox,InternalCommands}/`. Payments' two business jobs are in `Infrastructure/Configuration/Quartz/Jobs/`. The scheduler factory is configured with the instance name only (`StdSchedulerFactory` + `quartz.scheduler.instanceName`), no job-store setting appears in `QuartzStartup.cs`.

## Process managers

No class named `ProcessManager` or `Saga`, and no state-machine type, exists anywhere in `src/` (full-text search over `*.cs`). The long-running, multi-module processes are choreographies built from domain-event notification handlers, integration events and internal commands; they are written out as "Event flows" in each module file. The Payments projectors with `SubscriptionsManager` form the only continuous subscription (event store to read tables).

## Existing tests

Framework: NUnit 4.0.1 (`[Test]`, `[TestCase]`), FluentAssertions, NSubstitute, NetArchTest. Counts below are test methods found by scanning the attributes (a `[TestCase]` method counts once).

| Area | Unit | Integration | Architecture | Location |
|---|---|---|---|---|
| Administration | 6 | 4 | 22 | `Modules/Administration/Tests/` |
| Meetings | 86 | 24 | 22 | `Modules/Meetings/Tests/` |
| Payments | 21 | 5 (1 ignored) | 22 | `Modules/Payments/Tests/` |
| Registrations | 7 | 3 | 22 | `Modules/Registrations/Tests/` |
| UserAccess | 0 | 1 | 22 | `Modules/UserAccess/Tests/` |
| BuildingBlocks | 1 | 0 | 0 | `BuildingBlocks/Tests/Application.UnitTests/` |
| Cross-module | 0 | 1 | 8 | `Tests/IntegrationTests/`, `Tests/ArchTests/` |
| SUT helpers | | 3 data-preparation "tests" | | `Tests/SUT/TestCases/` |

Cross-module tests:
- `Tests/IntegrationTests/CreateMeetingGroup/CreateMeetingGroupTests.cs`: `CreateMeetingGroupScenario_WhenProposalIsAccepted`. Proposes a group through `IMeetingsModule`, polls (probe, 10 s) until Administration holds the proposal in status `ToVerify`, accepts it through `IAdministrationModule`, polls (15 s) until `GetAllMeetingGroupsQuery` returns the group. It exercises the whole choreography Meetings -> Administration -> Meetings with real Quartz jobs.
- `Tests/ArchTests/Modules/ModuleTests.cs`: `AdministrationModule_DoesNotHave_Dependency_On_Other_Modules`, `MeetingsModule_...`, `PaymentsModule_...`, `UserAccessModule_...` (no Registrations case).
- `Tests/ArchTests/Api/ApiTests.cs`: `AdministrationApi_DoesNotHaveDependency_ToOtherModules`, `MeetingsApi_...`, `PaymentsApi_...`, `UserAccessApi_...`.
- `Tests/SUT/` (system-under-test helpers, not assertions): `TestCases/CleanDatabaseTestCase.cs`, `TestCases/CreateMeeting.cs`, `TestCases/OnlyAdminTestCase.cs` (each one `[Test] Prepare()` that builds data through the module interfaces), helpers `UsersFactory`, `MeetingGroupsFactory`, `TestMeetingFactory`, `TestMeetingGroupManager`, `TestMeetingManager`, `TestPaymentsManager`, `TestPriceListManager`, `SeedWork/` (`DatabaseCleaner`, `AsyncOperationsHelper`, `Probing/{Poller,IProbe,Timeout,AssertErrorException}`), `Scripts/SeedPermissions.sql`.
- Shared integration-test base per module: `Tests/IntegrationTests/SeedWork/TestBase.cs` + `ExecutionContextMock.cs` (and `OutboxMessagesHelper.cs` for Administration).

Architecture rule set repeated in every module (`Tests/ArchTests/`): `Application/ApplicationTests.cs` (10 rules: commands and queries immutable; command/query handlers named `*CommandHandler` / `*QueryHandler` and not public; validators named `*Validator` and not public; internal commands have a `[JsonConstructor]` constructor; no direct MediatR `IRequestHandler`; commands with result do not return `Unit`), `Domain/DomainTests.cs` (9 rules: domain events and value objects immutable; non-root entities have no public members; entities do not reference other aggregate roots; entities have a private parameterless constructor; domain objects have only private constructors; value objects have a private constructor with state parameters; `DomainEvent` and `Rule` suffixes), `Module/LayersTests.cs` (3 rules on layer dependencies). No module has an API-level (HTTP) test; there is no test that issues a request against a controller.

## Observations (as found, not evaluated)

1. `GET /api/meetings/countries` requires permission `GetMeetingGroupProposals` (`Modules/Meetings/Countries/CountriesController.cs`) and ignores its `page`/`perPage` parameters.
2. `EnableMeetingCommenting` and `DisableMeetingCommenting` permissions exist in `users.Permissions` but are granted to no role in `SeedDatabase.sql`; the seed grants `MyMeetingsGroupsView` whereas the permission row is `MyMeetingGroupsView` (different spelling).
3. `UserRegistrationsController` is routed at `/userAccess/userRegistrations` (no `api/` prefix) and sits in the API's `Modules/UserAccess` folder although it dispatches Registrations commands.
4. `UserRegistration.Expire()` has no caller in production code (only in unit tests).
5. `MemberCreatedIntegrationEvent` is never published; UserAccess subscribes to it without a handler. Payments subscribes to `MeetingGroupProposalAcceptedIntegrationEvent` without a handler.
6. `MemberCreatedNotification` wraps `MeetingCreatedDomainEvent` (not `MemberCreatedDomainEvent`) and its handler is empty (`Modules/Meetings/Application/Members/CreateMember/`).
7. Payments' Quartz scheduler uses `quartz.scheduler.instanceName = "Meetings"`.
8. `ExpireSubscriptionPaymentsCommandHandler` selects `SubscriptionPayments` with `Date > now + 20 minutes` and status `WaitingForPayment`.
9. `Subscription_Buy_ThenRenew_ThenExpire_Test` is `[Ignore("Sometimes fails, to check why")]`.
10. Administration's and Meetings' proposal statuses use different literals (`ToVerify`/`Verified` vs `InVerification`/`Accepted`).
11. UserAccess `Application/Contracts/Roles.cs` defines `Admin`/`User` whereas the persisted roles are `Member`/`Administrator`.
12. Request DTO `CreateNewMeetingGroupRequest` exists in the API but no action uses it; `GetAllMeetingGroupsQuery` and `GetAllMeetingGroupProposalsQuery` differ in paging (`IPagedQuery` only on the latter).
