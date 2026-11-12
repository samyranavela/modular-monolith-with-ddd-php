# Administration module

Source: `src/Modules/Administration/` (Application, Domain, Infrastructure, IntegrationEvents, Tests). Paths below are relative to the .NET repository root (`modular-monolith-with-ddd/`) unless noted. See [README.md](README.md) for conventions.

Role: back-office verification of meeting group proposals proposed in the Meetings module. Keeps its own copy of members (`administration.Members`) and its own copy of proposals.

## Aggregates

| Aggregate root | Members (entities / VOs) | Source |
|---|---|---|
| `MeetingGroupProposal` (`Entity, IAggregateRoot`) | VOs: `MeetingGroupProposalId` (typed id), `MeetingGroupLocation`, `MeetingGroupProposalStatus` (`ToVerify`, `Verified`), `MeetingGroupProposalDecision` | `Modules/Administration/Domain/MeetingGroupProposals/` |
| `Member` (`Entity, IAggregateRoot`) | `MemberId` | `Modules/Administration/Domain/Members/` |

Other domain types: `UserId`, `IUserContext` (`Domain/Users/`), `IMeetingGroupProposalRepository`, `IMemberRepository`.

Business rules (`Domain/MeetingGroupProposals/Rules/`):
- `MeetingGroupProposalCanBeVerifiedOnceRule`
- `MeetingGroupProposalRejectionMustHaveAReasonRule`

## Commands

Base types: `CommandBase` / `CommandBase<T>` (public, from `Application/Contracts/`), `InternalCommandBase<T>` (internal, persisted in `administration.InternalCommands`, requires `[JsonConstructor]`).

| Command | Kind | Fields | Result | Source |
|---|---|---|---|---|
| `AcceptMeetingGroupProposalCommand` | public | `Guid MeetingGroupProposalId` | none | `Application/MeetingGroupProposals/AcceptMeetingGroupProposal/` |
| `RequestMeetingGroupProposalVerificationCommand` | internal | `Id, MeetingGroupProposalId, Name, Description, LocationCity, LocationCountryCode, ProposalUserId, ProposalDate` | `Guid` | `Application/MeetingGroupProposals/RequestMeetingGroupProposalVerification/` |
| `CreateMemberCommand` | internal | `Id, MemberId, Login, Email, FirstName, LastName, Name` | `Guid` | `Application/Members/CreateMember/` |

Infrastructure-level commands (module plumbing, same set in every module): `ProcessInboxCommand`, `ProcessOutboxCommand`, `ProcessInternalCommandsCommand` (`Infrastructure/Configuration/Processing/{Inbox,Outbox,InternalCommands}/`).

## Queries

| Query | Fields | Result | Source |
|---|---|---|---|
| `GetMeetingGroupProposalsQuery` | none | `List<MeetingGroupProposalDto>` | `Application/MeetingGroupProposals/GetMeetingGroupProposals/` |
| `GetMeetingGroupProposalQuery` | `Guid MeetingGroupProposalId` | `MeetingGroupProposalDto` | `Application/MeetingGroupProposals/GetMeetingGroupProposal/` |
| `GetMemberQuery` | `Guid MemberId` | `MemberDto` | `Application/Members/GetMember/` |

`GetMeetingGroupProposalQuery` and `GetMemberQuery` are not reachable from any REST endpoint (checked against `API/CompanyName.MyMeetings.API/Modules/`); they are used from tests (`Tests/IntegrationTests/`).

## Domain events

All derive from `DomainEventBase` (`BuildingBlocks/Domain/DomainEventBase.cs`). Source folder: `Modules/Administration/Domain/`.

| Domain event | Payload | Source |
|---|---|---|
| `MeetingGroupProposalVerificationRequestedDomainEvent` | `MeetingGroupProposalId` | `MeetingGroupProposals/Events/` |
| `MeetingGroupProposalAcceptedDomainEvent` | `MeetingGroupProposalId` | `MeetingGroupProposals/Events/` |
| `MeetingGroupProposalRejectedDomainEvent` (internal) | `MeetingGroupProposalId` | `MeetingGroupProposals/Events/` |
| `MemberCreatedDomainEvent` | `MemberId` | `Members/Events/` |

Domain notification (wrapper dispatched through the outbox): `MeetingGroupProposalAcceptedNotification : DomainNotificationBase<MeetingGroupProposalAcceptedDomainEvent>` (`Application/MeetingGroupProposals/AcceptMeetingGroupProposal/`).

## Integration events

| Direction | Event | Payload | Defined in | Handler / publisher |
|---|---|---|---|---|
| Published | `MeetingGroupProposalAcceptedIntegrationEvent` | `Id, OccurredOn, MeetingGroupProposalId` | `Modules/Administration/IntegrationEvents/MeetingGroupProposals/` | `MeetingGroupProposalAcceptedNotificationHandler` (`Application/MeetingGroupProposals/AcceptMeetingGroupProposal/`) calls `_eventsBus.Publish` |
| Consumed | `MeetingGroupProposedIntegrationEvent` (Meetings) | see [meetings.md](meetings.md) | `Modules/Meetings/IntegrationEvents/` | `MeetingGroupProposedIntegrationEventHandler` enqueues `RequestMeetingGroupProposalVerificationCommand` |
| Consumed | `NewUserRegisteredIntegrationEvent` (Registrations) | see [registrations.md](registrations.md) | `Modules/Registrations/IntegrationEvents/` | `Application/Members/NewUserRegisteredIntegrationEventHandler.cs` enqueues `CreateMemberCommand` |

Subscriptions registered in `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs` (2 events).

## Event flows (choreography; no process manager class exists)

1. Meetings publishes `MeetingGroupProposedIntegrationEvent` -> inbox -> `RequestMeetingGroupProposalVerificationCommand` (internal) -> creates `MeetingGroupProposal` (status `ToVerify`).
2. Admin calls `AcceptMeetingGroupProposalCommand` -> aggregate raises `MeetingGroupProposalAcceptedDomainEvent` -> notification handler publishes `MeetingGroupProposalAcceptedIntegrationEvent` through the outbox.
3. Registrations publishes `NewUserRegisteredIntegrationEvent` -> inbox -> `CreateMemberCommand` -> `Member`.

## REST endpoints

Controller: `API/CompanyName.MyMeetings.API/Modules/Administration/MeetingGroupProposals/MeetingGroupProposalsController.cs` (`[Route("api/administration/meetingGroupProposals")]`). Permissions in `API/.../Modules/Administration/AdministrationPermissions.cs`.

| Verb | Route | Permission | Dispatches |
|---|---|---|---|
| GET | `/api/administration/meetingGroupProposals` | `AcceptMeetingGroupProposal` | `GetMeetingGroupProposalsQuery` |
| PATCH | `/api/administration/meetingGroupProposals/{meetingGroupProposalId}/accept` | `AcceptMeetingGroupProposal` | `AcceptMeetingGroupProposalCommand` |

Seeded role grant: `AcceptMeetingGroupProposal` and `AdministrationsView` are granted to role `Administrator` only (`Database/CompanyName.MyMeetings.Database/Scripts/SeedDatabase.sql`).

## Database (schema `administration`)

Defined in `Database/CompanyName.MyMeetings.Database/Structure/administration/`.

| Table / view | Columns (type) | Notes |
|---|---|---|
| `administration.MeetingGroupProposals` | `Id` GUID PK, `Name` nvarchar(255), `Description` varchar(200) null, `LocationCity` nvarchar(50), `LocationCountryCode` nvarchar(3), `ProposalUserId` GUID, `ProposalDate` datetime, `StatusCode` nvarchar(50), `DecisionDate` datetime null, `DecisionUserId` GUID null, `DecisionCode` nvarchar(50) null, `DecisionRejectReason` nvarchar(250) null | EF mapping `Infrastructure/Domain/MeetingGroupProposals/MeetingGroupProposalEntityTypeConfiguration.cs` (owned: `_location`, `_status`, `_decision`) |
| `administration.Members` | `Id` GUID PK, `Login` nvarchar(100), `Email` nvarchar(255), `FirstName`, `LastName`, `Name` | |
| `administration.InboxMessages` | `Id`, `OccurredOn` datetime2, `Type` varchar(255), `Data` varchar(max), `ProcessedDate` null | |
| `administration.OutboxMessages` | same columns as inbox | |
| `administration.InternalCommands` | `Id`, `EnqueueDate`, `Type`, `Data`, `ProcessedDate` null, `Error` nvarchar(max) null | |
| view `administration.v_MeetingGroupProposals`, `administration.v_Members` | read models used by Dapper queries | |

## Quartz jobs

Scheduler: one per module (`Infrastructure/Configuration/Quartz/QuartzStartup.cs`). Trigger: cron `0/2 * * ? * *` (every 2 s), or a fixed interval in ms when `internalProcessingPoolingInterval` is supplied (used by tests).

| Job | Class | Purpose |
|---|---|---|
| Outbox | `ProcessOutboxJob` | publish outbox messages to the event bus |
| Inbox | `ProcessInboxJob` | dispatch received integration events |
| Internal commands | `ProcessInternalCommandsJob` | execute enqueued internal commands |

No domain-specific (business) job.

## Process managers

None found. No class matching `ProcessManager` or `Saga` exists in the source tree (full-text search over `src/**/*.cs`).

## Existing tests

NUnit 4, FluentAssertions, NetArchTest. Folder `Modules/Administration/Tests/`.

Unit tests (`UnitTests/`):
- `MeetingGroupProposals/MeetingGroupProposalTests.cs`: `CreateProposalToVerify_IsSuccessful`, `AcceptProposal_WhenDecisionIsNotMade_IsSuccessful`, `AcceptProposal_WhenDecisionIsMade_CanBeVerifiedOnlyOnce`, `RejectProposal_WhenDecisionIsMade_CanBeVerifiedOnlyOnce`, `RejectProposal_WithoutProvidedReason_CannotBeRejected`
- `Members/MemberTests.cs`: `CreateMember_IsSuccessful`

Integration tests (`IntegrationTests/`, run against a real SQL Server):
- `MeetingGroupProposals/MeetingGroupProposalTests.cs`: `RequestMeetingGroupProposalVerification_Test`, `AcceptMeetingGroupProposal_WhenProposalIsNotAccepted_IsSuccessful`, `AcceptMeetingGroupProposal_WhenProposalIsAlreadyAccepted_BreaksMeetingGroupProposalCanBeVerifiedOnceRule`
- `Members/CreateMemberTests.cs`: `CreateMember_Test`
- helpers: `SeedWork/OutboxMessagesHelper.cs`, `MeetingGroupProposalSampleData.cs`, `MemberSampleData.cs`

Architecture tests (`ArchTests/`): 10 application rules (`Application/ApplicationTests.cs`: commands and queries immutable, handler/validator naming, handlers and validators not public, internal commands have `[JsonConstructor]`, no direct MediatR `IRequestHandler`, commands with result do not return `Unit`), 9 domain rules (`Domain/DomainTests.cs`: events/value objects immutable, non-root entities have no public members, no reference to another aggregate root, private parameterless entity ctor, private constructors, `DomainEvent`/`Rule` suffixes), 3 layer rules (`Module/LayersTests.cs`: domain does not depend on application or infrastructure; application does not depend on infrastructure). Identical set exists in every module.

Cross-module scenario involving this module: `Tests/IntegrationTests/CreateMeetingGroup/CreateMeetingGroupTests.cs` (see [README.md](README.md)).
