# UserAccess module

Source: `src/Modules/UserAccess/` (Application, Domain, Infrastructure, IntegrationEvents, Tests). Paths are relative to the .NET repository root unless noted. See [README.md](README.md) for conventions.

Role: user store, authentication (IdentityServer4 resource-owner password flow), role/permission lookup used by the API authorization handler, and listing of the mock e-mails table.

## Aggregates

| Aggregate root | Members | Source |
|---|---|---|
| `User` (`Entity, IAggregateRoot`) | fields `_login, _password, _email, _isActive, _firstName, _lastName, _name, _roles`; factories `User.CreateAdmin(...)` and `User.CreateUser(...)`; VOs `UserId` (typed id), `UserRole` (`Member`, `Administrator`) | `Modules/UserAccess/Domain/Users/` |

No business-rule classes (`Rules/` folder absent). `IUserRepository` in `Domain/Users/`.

## Commands

| Command | Kind | Fields | Result | Source |
|---|---|---|---|---|
| `AuthenticateCommand` | public | `Login, Password` | `AuthenticationResult` | `Application/Authentication/Authenticate/` (validator `AuthenticateCommandValidator`; `PasswordManager` helper) |
| `CreateUserCommand` | public | `UserId, Login, Email, FirstName, LastName, Password` | none | `Application/Users/CreateUser/` |
| `AddAdminUserCommand` | public | `Login, Password, FirstName, LastName, Name, Email` | none | `Application/Users/AddAdminUser/` |

Usage: `AuthenticateCommand` is only called by `Infrastructure/IdentityServer/ResourceOwnerPasswordValidator.cs`; `CreateUserCommand` is called by Registrations through `UserAccessGateway`; `AddAdminUserCommand` is referenced only from `Tests/SUT/Helpers/UsersFactory.cs` (no API usage).

Contract constants: `CustomClaimTypes` (`roles`, `email`, `name`), `Roles` (`Admin`, `User`) in `Application/Contracts/`. Note these `Roles` constants (`Admin`, `User`) differ from the `UserRole` values (`Member`, `Administrator`) stored in the database.

## Queries

| Query | Fields | Result | Backing SQL | Source |
|---|---|---|---|---|
| `GetAuthenticatedUserQuery` | none (user from execution context) | `UserDto` | `users.v_Users` | `Application/Users/GetAuthenticatedUser/` |
| `GetUserQuery` | `Guid UserId` | `UserDto` | `users.v_Users` | `Application/Users/GetUser/` |
| `GetAuthenticatedUserPermissionsQuery` | none | `List<UserPermissionDto>` | same view, filtered on the user id of the execution context (`IExecutionContextAccessor`) | `Application/Authorization/GetAuthenticatedUserPermissions/` |
| `GetUserPermissionsQuery` | `Guid UserId` | `List<UserPermissionDto>` (`Code`) | `SELECT PermissionCode FROM users.v_UserPermissions WHERE UserId = @UserId` | `Application/Authorization/GetUserPermissions/` |
| `GetAllEmailsQuery` | none | `List<EmailDto>` | `SELECT ... FROM app.Emails` | `Application/Emails/` |

`GetUserPermissionsQuery` is called by the API authorization handler `API/.../Configuration/Authorization/HasPermissionAuthorizationHandler.cs` on every protected request. `GetUserQuery` has no REST endpoint.

## Domain events

| Domain event | Payload | Source |
|---|---|---|
| `UserCreatedDomainEvent` | `UserId Id` | `Domain/Users/Events/UserCreatedDomainEvent.cs` |

No notification classes (`*Notification.cs`) exist in `Application/`.

## Integration events

| Direction | Event | Notes |
|---|---|---|
| Published | none | `Modules/UserAccess/IntegrationEvents/` contains no event class |
| Consumed (declared) | `MemberCreatedIntegrationEvent` (defined in Meetings, `Modules/Meetings/IntegrationEvents/MemberCreatedIntegrationEvent.cs`) | subscribed in `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs:20`; no `INotificationHandler<MemberCreatedIntegrationEvent>` exists in UserAccess, and no code in the solution publishes `MemberCreatedIntegrationEvent` (search over `src/**/*.cs`) |

## Authentication and authorization (cross-cutting, owned by this module)

- IdentityServer4 configured in `Infrastructure/Configuration/Identity/IdentityConfiguration.cs`: in-memory resources, in-memory persisted grants, developer signing credential, `ProfileService`, `ResourceOwnerPasswordValidator`. Authority `http://localhost:5000`, API name `myMeetingsAPI`, `RequireHttpsMetadata = false`.
- Client (`Infrastructure/IdentityServer/IdentityServerConfig.cs`): `ClientId = "ro.client"`, grant `ResourceOwnerPassword`, secret `secret` (SHA256), scopes `all`, `openid`, `profile`. API resource `myMeetingsAPI` with scope `all`. Identity resource `roles`.
- Token endpoint (IdentityServer default, not a controller): `POST /connect/token` (see `API/RequestExamples/Authentication.http`).
- `ProfileService` issues claims `roles`, `name`, `email`.
- API side: attribute `[HasPermission("...")]` / `[NoPermissionRequired]` (`API/.../Configuration/Authorization/`), `AuthorizationChecker.CheckAllEndpoints()` at startup verifies every endpoint carries one of the two attributes.
- Other infrastructure: `AesDataProtector` (`Infrastructure/Configuration/Security/`, key from `Security:TextEncryptionKey` in `appsettings.json`), e-mail sender `BuildingBlocks/Infrastructure/Emails/EmailSender.cs` (registered through `Infrastructure/Configuration/Email/`), which does `INSERT INTO [app].[Emails]` instead of sending mail.

## REST endpoints

Controllers in `API/CompanyName.MyMeetings.API/Modules/UserAccess/`.

| Verb | Route | Permission | Dispatches | Source |
|---|---|---|---|---|
| GET | `/api/userAccess/authenticatedUser` | `[NoPermissionRequired]` | `GetAuthenticatedUserQuery` | `AuthenticatedUserController.cs` |
| GET | `/api/userAccess/authenticatedUser/permissions` | `[NoPermissionRequired]` | `GetAuthenticatedUserPermissionsQuery` | `AuthenticatedUserController.cs` |
| GET | `/api/userAccess/emails` | `[NoPermissionRequired]` | `GetAllEmailsQuery` | `EmailsController.cs` |
| POST | `/connect/token` | anonymous (IdentityServer) | `AuthenticateCommand` through `ResourceOwnerPasswordValidator` | `Infrastructure/IdentityServer/` |

`UserRegistrationsController` (`/userAccess/userRegistrations`) is in this API folder but belongs to the Registrations module; see [registrations.md](registrations.md).

### Permission catalogue and role grants

`users.Permissions` and `users.RolesToPermissions` are filled by `Database/CompanyName.MyMeetings.Database/Scripts/SeedDatabase.sql` (and `Tests/SUT/Scripts/SeedPermissions.sql` for tests). Two roles: `Member`, `Administrator`.

- `Administrator`: `AcceptMeetingGroupProposal`, `AdministrationsView`, `CreatePriceListItem`, `ActivatePriceListItem`, `DeactivatePriceListItem`, `ChangePriceListItemAttributes`, `GetPriceListItem`.
- `Member` (all Meetings permissions except the ones listed below, plus Payments): `GetMeetingGroupProposals, ProposeMeetingGroup, CreateNewMeeting, EditMeeting, AddMeetingAttendee, RemoveMeetingAttendee, AddNotAttendee, ChangeNotAttendeeDecision, SignUpMemberToWaitlist, SignOffMemberFromWaitlist, SetMeetingHostRole, SetMeetingAttendeeRole, CancelMeeting, GetAllMeetingGroups, EditMeetingGroupGeneralAttributes, JoinToGroup, LeaveMeetingGroup, AddMeetingComment, EditMeetingComment, RemoveMeetingComment, AddMeetingCommentReply, LikeMeetingComment, UnlikeMeetingComment, GetAuthenticatedMemberMeetingGroups, GetMeetingGroupDetails, GetMeetingDetails, GetMeetingAttendees, MyMeetingsGroupsView, SubscriptionView, EmailsView, AllMeetingGroupsView, MyMeetingsView, GetAuthenticatedMemberMeetings, RegisterPayment, BuySubscription, RenewSubscription, GetAuthenticatedPayerSubscription, GetPriceListItem`.
- Permissions inserted in `users.Permissions` but granted to no role in `SeedDatabase.sql`: `EnableMeetingCommenting`, `DisableMeetingCommenting`. `MyMeetingGroupsView` is inserted in `users.Permissions`, whereas the grant row uses `MyMeetingsGroupsView` (different spelling) (`SeedDatabase.sql`).
- Seed users: `testMember@mail.com` (role `Member`) and `testAdmin@mail.com` (role `Administrator`); also inserted in `registrations.UserRegistrations` (status `Confirmed`), and for the member in `meetings.Members` and `payments.Payers`.

## Database (schema `users`; table `app.Emails` is read here)

Defined in `Database/CompanyName.MyMeetings.Database/Structure/users/`.

| Table / view | Columns (type) |
|---|---|
| `users.Users` | `Id` GUID PK, `Login` nvarchar(100), `Email` nvarchar(255), `Password` nvarchar(255), `IsActive` bit, `FirstName` nvarchar(50), `LastName` nvarchar(50), `Name` nvarchar(255) |
| `users.UserRoles` | `UserId` GUID, `RoleCode` nvarchar(50) (EF owned collection `_roles`) |
| `users.Permissions` | `Code` varchar(50) PK, `Name` varchar(100), `Description` varchar(255) null |
| `users.RolesToPermissions` | `RoleCode` varchar(50), `PermissionCode` varchar(50), composite PK |
| `users.InboxMessages`, `users.OutboxMessages`, `users.InternalCommands` | same shape as in every module (see [administration.md](administration.md)) |
| views `users.v_Users`, `users.v_UserRoles`, `users.v_UserPermissions` | read models |
| `app.Emails` (shared schema `app`) | `Id` GUID PK, `From`, `To`, `Subject` nvarchar(255), `Content` nvarchar(max), `Date` datetime |

EF mapping: `Infrastructure/Domain/Users/UserEntityTypeConfiguration.cs`.

## Quartz jobs

The same three generic jobs as every module: `ProcessOutboxJob`, `ProcessInboxJob`, `ProcessInternalCommandsJob` (`Infrastructure/Configuration/Quartz/QuartzStartup.cs`, cron `0/2 * * ? * *` or fixed interval). No business job.

## Process managers

None found.

## Existing tests

Folder `Modules/UserAccess/Tests/`.

- Unit tests: none (no `UnitTests/` folder).
- Integration (`IntegrationTests/Users/CreateUserTests.cs`): `CreateUser_Test`. No integration test covers `AuthenticateCommand`, `AddAdminUserCommand`, permissions or e-mails.
- Architecture: same 10 + 9 + 3 rules as the other modules (see [administration.md](administration.md)).
