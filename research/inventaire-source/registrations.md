# Registrations module

Source: `src/Modules/Registrations/` (Application, Domain, Infrastructure, IntegrationEvents, Tests). Paths are relative to the .NET repository root unless noted. See [README.md](README.md) for conventions.

Role: self-registration of a user with e-mail confirmation. On confirmation it asks UserAccess to create the user (synchronous call through a gateway, not through the event bus) and, at registration time, announces the new user to the other modules via an integration event.

## Aggregates

| Aggregate root | Members | Source |
|---|---|---|
| `UserRegistration` (`Entity, IAggregateRoot`) | VOs: `UserRegistrationId`, `UserRegistrationStatus` (`WaitingForConfirmation`, `Confirmed`, `Expired`). Factory `RegisterNewUser(...)`, methods `Confirm()`, `Expire()` | `Modules/Registrations/Domain/UserRegistrations/` |

Domain service interface: `IUsersCounter` (implemented by `Application/UserRegistrations/UsersCounter.cs`, used by the unique-login rule).

Business rules (`Domain/UserRegistrations/Rules/`):
- `UserLoginMustBeUniqueRule`
- `UserRegistrationCannotBeConfirmedMoreThanOnceRule`
- `UserRegistrationCannotBeConfirmedAfterExpirationRule`
- `UserRegistrationCannotBeExpiredMoreThanOnceRule`
- `UserCannotBeCreatedWhenRegistrationIsNotConfirmedRule` (file present in the rules folder)

Note: `Expire()` and `UserRegistrationExpiredDomainEvent` exist and are unit-tested, but no command, handler or Quartz job in the module calls `Expire()` (search over `Modules/Registrations/**/*.cs`; only `Tests/UnitTests/.../UserRegistrationTests.cs` calls it).

## Commands

| Command | Kind | Fields | Result | Source |
|---|---|---|---|---|
| `RegisterNewUserCommand` | public | `Login, Password, Email, FirstName, LastName, ConfirmLink` | `Guid` | `Application/UserRegistrations/RegisterNewUser/` |
| `ConfirmUserRegistrationCommand` | public | `Guid UserRegistrationId` | none | `Application/UserRegistrations/ConfirmUserRegistration/` |
| `SendUserRegistrationConfirmationEmailCommand` | internal | `Id, UserRegistrationId, Email, ConfirmLink` | none | `Application/UserRegistrations/SendUserRegistrationConfirmationEmail/` |

Helper: `PasswordManager` (`Application/UserRegistrations/RegisterNewUser/PasswordManager.cs`) hashes the password. Plumbing commands: `ProcessInboxCommand`, `ProcessOutboxCommand`, `ProcessInternalCommandsCommand`.

No FluentValidation validator exists in this module.

## Queries

| Query | Fields | Result | Source |
|---|---|---|---|
| `GetUserRegistrationQuery` | `Guid UserRegistrationId` | `UserRegistrationDto` (read through `UserRegistrationProvider`) | `Application/UserRegistrations/GetUserRegistration/` |

Not reachable from a REST endpoint; also used internally by `UserRegistrationConfirmedNotificationHandler` (through `UserRegistrationProvider.GetById`).

## Domain events

Source: `Modules/Registrations/Domain/UserRegistrations/Events/`.

| Domain event | Payload |
|---|---|
| `NewUserRegisteredDomainEvent` | `UserRegistrationId, Login, Email, FirstName, LastName, Name, RegisterDate, ConfirmLink` |
| `UserRegistrationConfirmedDomainEvent` | `UserRegistrationId` |
| `UserRegistrationExpiredDomainEvent` | `UserRegistrationId` |

Notifications (`DomainNotificationBase<T>`): `NewUserRegisteredNotification`, `UserRegistrationConfirmedNotification`.

## Integration events

| Direction | Event | Payload | Source / handler |
|---|---|---|---|
| Published | `NewUserRegisteredIntegrationEvent` | `Id, OccurredOn, UserId, Login, Email, FirstName, LastName, Name` | defined in `Modules/Registrations/IntegrationEvents/NewUserRegisteredIntegrationEvent.cs`; published by `NewUserRegisteredPublishEventHandler` (`Application/UserRegistrations/RegisterNewUser/`) |
| Consumed | none | | `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs` has the only subscription line commented out (`//// SubscribeToIntegrationEvent<MemberCreatedIntegrationEvent>`) |

The project also contains an empty placeholder `Modules/Registrations/IntegrationEvents/Class1.cs`.

Consumers of `NewUserRegisteredIntegrationEvent`: Meetings (creates `Member`), Administration (creates `Member`), Payments (creates `Payer`). See the respective module files.

## Event flows

1. `RegisterNewUserCommand` -> `UserRegistration.RegisterNewUser` -> `NewUserRegisteredDomainEvent` -> two handlers on `NewUserRegisteredNotification`: `NewUserRegisteredEnqueueEmailConfirmationHandler` (enqueues `SendUserRegistrationConfirmationEmailCommand`) and `NewUserRegisteredPublishEventHandler` (publishes `NewUserRegisteredIntegrationEvent`).
2. `ConfirmUserRegistrationCommand` -> `UserRegistrationConfirmedDomainEvent` -> `UserRegistrationConfirmedNotificationHandler` -> `IUserCreator.Create` -> `UserAccessGateway` (`Infrastructure/Users/UserAccessGateway.cs`) calls `IUserAccessModule.ExecuteCommandAsync(CreateUserCommand)` synchronously inside the module call (cross-module call through the module's public interface).

No process manager class exists.

## REST endpoints

Controller: `API/CompanyName.MyMeetings.API/Modules/UserAccess/UserRegistrationsController.cs`. Note the file lives in the API's `UserAccess` folder and the route has no `api/` prefix: `[Route("userAccess/[controller]")]`.

| Verb | Route | Permission | Dispatches |
|---|---|---|---|
| POST | `/userAccess/userRegistrations` | `[NoPermissionRequired]` | `RegisterNewUserCommand` (body `RegisterNewUserRequest`: `Login, Password, Email, FirstName, LastName, ConfirmLink`) |
| PATCH | `/userAccess/userRegistrations/{userRegistrationId}/confirm` | `[NoPermissionRequired]` | `ConfirmUserRegistrationCommand` |

## Database (schema `registrations`)

Defined in `Database/CompanyName.MyMeetings.Database/Structure/registrations/`.

| Table / view | Columns (type) |
|---|---|
| `registrations.UserRegistrations` | `Id` GUID PK, `Login` nvarchar(100), `Email` nvarchar(255), `Password` nvarchar(255), `FirstName` nvarchar(50), `LastName` nvarchar(50), `Name` nvarchar(255), `StatusCode` varchar(50), `RegisterDate` datetime, `ConfirmedDate` datetime null |
| `registrations.InboxMessages` | `Id`, `OccurredOn` datetime2, `Type` varchar(255), `Data` varchar(max), `ProcessedDate` null |
| `registrations.OutboxMessages` | same columns as inbox |
| `registrations.InternalCommands` | `Id`, `EnqueueDate`, `Type`, `Data`, `ProcessedDate` null, `Error` nvarchar(max) null |
| view `registrations.v_UserRegistrations` | read model |

EF mapping: `Infrastructure/Domain/UserRegistrations/UserRegistrationEntityTypeConfiguration.cs` (owned `_status`).

## Quartz jobs

Same three generic jobs as every module (`Infrastructure/Configuration/Quartz/QuartzStartup.cs`), cron `0/2 * * ? * *` or fixed interval: `ProcessOutboxJob`, `ProcessInboxJob`, `ProcessInternalCommandsJob`. No business job (nothing expires registrations automatically).

## Process managers

None found.

## Existing tests

Folder `Modules/Registrations/Tests/`.

Unit (`UnitTests/UserRegistrations/UserRegistrationTests.cs`): `NewUserRegistration_WithUniqueLogin_IsSuccessful`, `NewUserRegistration_WithoutUniqueLogin_BreaksUserLoginMustBeUniqueRule`, `ConfirmingUserRegistration_WhenWaitingForConfirmation_IsSuccessful`, `UserRegistration_WhenIsConfirmed_CannotBeConfirmedAgain`, `UserRegistration_WhenIsExpired_CannotBeConfirmed`, `ExpiringUserRegistration_WhenWaitingForConfirmation_IsSuccessful`, `UserRegistration_WhenIsExpired_CannotBeExpiredAgain`.

Integration (`IntegrationTests/UserRegistrations/`): `UserRegistrationTests.RegisterNewUserCommand_Test`, `ConfirmUserRegistrationTests.ConfirmUserRegistration_Test`, `SendUserRegistrationConfirmationEmailTests.SendUserRegistrationConfirmationEmail_Test`.

Architecture: same 10 + 9 + 3 rules as the other modules (see [administration.md](administration.md)).
