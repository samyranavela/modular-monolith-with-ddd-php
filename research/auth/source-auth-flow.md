# Source authentication and authorization flow (.NET project)

Scope: facts about what the .NET source does. Paths are relative to
`/home/sranavela/workspace/poc/modular-monolith-with-ddd/src` (read-only).
Items marked **[not verified]** are inferences or library behaviour not confirmed against library source in this run.

## 1. Topology

- IdentityServer4 4.1.2 is embedded in the same ASP.NET Core process as the API
  (`Modules/UserAccess/Infrastructure/CompanyName.MyMeetings.Modules.UserAccess.Infrastructure.csproj`).
  `services.ConfigureIdentityService()` (called in `API/CompanyName.MyMeetings.API/Startup.cs`) registers it and
  `app.AddIdentityService()` calls `app.UseIdentityServer()`
  (`Modules/UserAccess/Infrastructure/Configuration/Identity/IdentityConfiguration.cs`).
- The same process also validates tokens with `IdentityServer4.AccessTokenValidation` 3.0.1:
  `AddIdentityServerAuthentication("Bearer", ...)` with `Authority = "http://localhost:5000"`,
  `ApiName = "myMeetingsAPI"`, `RequireHttpsMetadata = false` (same file). The authority URL is hard-coded.
- Storage: `AddInMemoryIdentityResources/ApiScopes/ApiResources/Clients` and `AddInMemoryPersistedGrants()`.
  No persistent grant store.
- Signing key: `AddDeveloperSigningCredential()`. A generated key file `API/CompanyName.MyMeetings.API/tempkey.rsa`
  (JSON with `KeyId` and RSA `Parameters`) is committed in the repository. Do not copy this key. Algorithm is
  presumably RS256 **[not verified]**.
- `Startup.Configure` has `app.UseAuthentication()` commented out and only `app.UseAuthorization()`. Whether
  `UseIdentityServer()` installs authentication middleware itself is **[not verified]**; the `HasPermission` policy also
  pins `AddAuthenticationSchemes("Bearer")`, so policy-protected endpoints authenticate through that scheme regardless.
- CORS: `AllowAnyOrigin/AnyHeader/AnyMethod`.

## 2. Token endpoint and client configuration

Example requests (`API/RequestExamples/Authentication.http`):

```
POST {{baseUrl}}/connect/token
Content-Type: application/x-www-form-urlencoded

grant_type=password&username=testMember@mail.com&password=testMemberPass&client_id=ro.client&client_secret=secret
```

A second example does the same for `testAdmin@mail.com` / `testAdminPass`. No `scope` parameter is sent.

`Modules/UserAccess/Infrastructure/IdentityServer/IdentityServerConfig.cs`:

| Item | Value |
|---|---|
| Client | `ClientId = "ro.client"`, `AllowedGrantTypes = GrantTypes.ResourceOwnerPassword` |
| Client secret | `new Secret("secret".Sha256())` (stored hashed, single shared secret) |
| Allowed scopes | `all`, `openid`, `profile` |
| ApiScope | `all` ("Can Do All") |
| ApiResource | `myMeetingsAPI`, `Scopes = { "all" }`, no `UserClaims` |
| IdentityResources | `openid`, `profile`, and custom `roles` (claim `roles`) |
| Other client properties | none set (all defaults) |

Defaults that therefore apply (IdentityServer4 4.1.2, `src/Storage/src/Models/Client.cs`, verified):
`AccessTokenLifetime = 3600`, `AccessTokenType = Jwt`, `AllowOfflineAccess = false`,
`RequireClientSecret = true`, `IdentityTokenLifetime = 300`, `RefreshTokenUsage = OneTimeOnly`,
`SlidingRefreshTokenLifetime = 1296000`, `AbsoluteRefreshTokenLifetime = 2592000`,
`UpdateAccessTokenClaimsOnRefresh = false`.

Duende docs for the successor product (the IS4 docs redirect there) state: when `scope` is omitted, the token is issued
for all explicitly allowed scopes; required password-grant parameters are `client_id`, `grant_type`, `username`,
`password`; `client_secret` is for confidential clients (body or Basic header).
Source: https://docs.duendesoftware.com/identityserver/v7/reference/endpoints/token/

Consequences for the source:

- **Token lifetime: 3600 s** (default, not set explicitly).
- **No refresh token.** `AllowOfflineAccess` is false and the client never allows `offline_access`. Duende docs: a
  refresh token is requested by adding the `offline_access` scope and the client must have `AllowOfflineAccess = true`
  (https://docs.duendesoftware.com/identityserver/v7/tokens/refresh/). Clients re-authenticate with
  username/password after expiry.
- The success response body shape (`access_token`, `expires_in`, `token_type`, `scope`) was not captured from IS4 docs in
  this run **[not verified]**; it follows RFC 6749 section 5.1.

## 3. Resource owner validation (`/connect/token`, grant `password`)

`ResourceOwnerPasswordValidator` (registered transient, `IdentityConfiguration.cs`):

1. Calls `IUserAccessModule.ExecuteCommandAsync(new AuthenticateCommand(userName, password))`.
2. On failure: `GrantValidationResult(TokenRequestErrors.InvalidGrant, authenticationResult.AuthenticationError)`.
3. On success: `GrantValidationResult(user.Id.ToString(), "forms", user.Claims)`, i.e. subject = user GUID,
   authentication method = `forms`.

`AuthenticateCommandHandler` (`Modules/UserAccess/Application/Authentication/Authenticate/`):

- Validator: `Login` and `Password` not empty (`AuthenticateCommandValidator`).
- SQL on view `[users].[v_Users]` filtering `Login = @Login` (`Login` column, not `Email`; the seed data uses the email as
  login).
- Failure messages: `"Incorrect login or password"` (unknown login, or wrong password), `"User is not active"`
  (`IsActive = false`). The unknown-login and wrong-password paths share one message.
- Password check: `PasswordManager.VerifyHashedPassword`. Format (from the code): Base64 of 49 bytes =
  `0x00` marker byte + 16-byte salt + 32-byte subkey; PBKDF2 (`Rfc2898DeriveBytes`) with HMAC-SHA256 and 1000 iterations.
  Constant-time style compare. `Registrations` has its own `PasswordManager` for hashing at registration
  (`Modules/Registrations/Application/UserRegistrations/RegisterNewUser/PasswordManager.cs`; not diffed against the
  UserAccess one).
- Claims attached to the user: only `name` and `email` (`CustomClaimTypes.Name = "name"`, `Email = "email"`).

Seed: `Database/CompanyName.MyMeetings.Database/Scripts/SeedDatabase.sql` inserts `testMember@mail.com` (role `Member`)
and an administrator (role `Administrator`), with password hashes in the format above.

## 4. Claims in the issued token

`ProfileService` (`IdentityServer/ProfileService.cs`):

- `GetProfileDataAsync`: adds every subject claim of type `roles`, then `Single` claim `name` and `Single` claim `email`.
  It ignores `RequestedClaimTypes`.
- `IsActiveAsync`: returns `context.IsActive` unchanged (no re-check of user state against the DB).

Verified IS4 4.1.2 behaviour (`DefaultClaimsService.GetAccessTokenClaimsAsync`):

- Always: `client_id`, one `scope` claim per granted scope; for a user token: `sub`, `auth_time`, `idp`, `amr` entries.
- `GetProfileDataAsync` is called for the access token even when the requested-claim-type list is empty (no count check),
  and the result passes only through a protocol-claim filter. With this profile service, `name` and `email` therefore
  reach the access token **[inferred from the two code paths]**.
- `aud` is not added in this method; where it is added was not checked.

Notable divergence inside the source: **no `roles` claim is ever issued.** `ProfileService` copies `roles` claims from the
subject, but `AuthenticateCommandHandler` only builds `name` and `email`. The `roles` identity resource and
`CustomClaimTypes.Roles` exist but are unused for tokens. The `Roles` class
(`Application/Contracts/Roles.cs`: `Admin`, `User`) does not match the role codes stored and seeded
(`Member`, `Administrator`; `Domain/Users/UserRole.cs`).

Effective access-token claims (best reading): `sub` (user GUID), `name`, `email`, `client_id`, `scope`
(`all`, `openid`, `profile` when no scope requested), `auth_time`, `idp`, `amr=forms`, plus standard JWT claims added by IS4
(`iss`, `iat`, `exp`, `nbf`, `aud`) **[not verified]**. No permissions, no roles.

## 5. How authorization reaches the API (permissions from UserAccess)

`API/CompanyName.MyMeetings.API/Configuration/Authorization/`:

- `[HasPermission("Code")]` is an `AuthorizeAttribute` bound to policy `"HasPermission"` (class or method level).
- `Startup.ConfigureServices`: policy `HasPermission` with `HasPermissionAuthorizationRequirement` and
  `AddAuthenticationSchemes("Bearer")`; handler `HasPermissionAuthorizationHandler` (scoped).
- Handler flow: reads the `HasPermissionAttribute` from endpoint metadata, calls
  `IUserAccessModule.ExecuteQueryAsync(new GetUserPermissionsQuery(_executionContextAccessor.UserId))`, then grants if any
  returned `UserPermissionDto.Code` equals the attribute name.
- `ExecutionContextAccessor.UserId` is read from the single `sub` claim of `HttpContext.User` (`Guid.Parse`); it throws
  `ApplicationException("User context is not available")` otherwise.
- Permission query: `SELECT PermissionCode FROM [users].[v_UserPermissions] WHERE UserId = @UserId`. The view is
  `DISTINCT UserId, PermissionCode` over `users.UserRoles JOIN users.RolesToPermissions ON RoleCode`.
  Tables: `users.Permissions(Code, Name, Description)`, `users.RolesToPermissions(RoleCode, PermissionCode)`,
  `users.UserRoles(UserId, RoleCode)`. Seeded in `Tests/SUT/Scripts/SeedPermissions.sql` (role `Member` gets the
  meetings/payments member permissions; role `Administrator` gets `AcceptMeetingGroupProposal`, `AdministrationsView`,
  price-list item permissions).
- **Permissions are looked up on every protected request from the database; they are not carried in the token.** Role or
  permission changes take effect on the next request.
- **Release-build quirk:** `AuthorizeAsync` is wrapped in `#if !DEBUG return Task.FromResult(true);`, so in non-DEBUG
  builds every `HasPermission` check passes for any authenticated caller.
- Permission code constants live in `API/.../Modules/{Meetings,Administration,Payments}/*Permissions.cs`.
- Startup guard: `AuthorizationChecker.CheckAllEndpoints()` reflects over all `ControllerBase` subclasses and throws at
  startup if a public action has neither `[HasPermission]` (method or class level) nor `[NoPermissionRequired]`.
- Anonymous endpoints use `[NoPermissionRequired]` + `[AllowAnonymous]`: `POST userAccess/UserRegistrations`,
  `PATCH userAccess/UserRegistrations/{id}/confirm`, `GET api/userAccess/emails`.
  `GET api/userAccess/authenticatedUser` and `.../permissions` use `[NoPermissionRequired]` only (permissions of the
  caller are returned by `GetAuthenticatedUserPermissionsQuery`, which relies on the execution context user).
- The token endpoint itself is IdentityServer's, outside controllers.

## 6. User registration and activation (Registrations module -> UserAccess)

1. `POST userAccess/UserRegistrations` (anonymous; body `Login, Password, Email, FirstName, LastName, ConfirmLink`) ->
   `RegisterNewUserCommand` via `IRegistrationsModule`.
2. `RegisterNewUserCommandHandler` hashes the password (PBKDF2 format above) and calls
   `UserRegistration.RegisterNewUser(...)`. Rule `UserLoginMustBeUniqueRule` (via `IUsersCounter`) throws a business rule
   error if the login exists. Status becomes `WaitingForConfirmation`, `_name = "{first} {last}"`. Domain event
   `NewUserRegisteredDomainEvent` carries the confirm link.
3. Notification handlers: `NewUserRegisteredPublishEventHandler` (integration event) and
   `NewUserRegisteredEnqueueEmailConfirmationHandler`, which enqueues `SendUserRegistrationConfirmationEmailCommand`
   through the module's internal commands scheduler (asynchronous, processed by Quartz jobs).
4. `SendUserRegistrationConfirmationEmailCommandHandler` sends an email with link
   `{ConfirmLink}{UserRegistrationId}`. **The confirmation credential is the registration GUID itself**; there is no
   separate signed or random token. `ConfirmLink` is client-supplied in the request body.
5. `PATCH userAccess/UserRegistrations/{userRegistrationId}/confirm` (anonymous) ->
   `ConfirmUserRegistrationCommand` -> `UserRegistration.Confirm()`. Rules: not already confirmed, not expired. Status
   `Confirmed`, `UserRegistrationConfirmedDomainEvent`.
6. `UserRegistrationConfirmedNotificationHandler` loads the registration (SQL) and calls `IUserCreator.Create(...)`,
   implemented by `UserAccessGateway` (`Registrations/Infrastructure/Users/`), which executes UserAccess
   `CreateUserCommand(userRegistrationId, login, email, firstName, lastName, passwordHash)`. The user keeps the
   registration id as its own id.
7. `User.CreateUser` creates the aggregate with role `UserRole.Member`, `_isActive = true`, name = `"{first} {last}"`, and
   raises `UserCreatedDomainEvent`. No email/password re-hash: the hash computed at step 2 is copied.
8. `User.CreateAdmin` (role `Administrator`) is used by `AddAdminUserCommand` (admin seeding; not traced further).
9. `IsActive`: the domain `User` has no operation to deactivate; a user is created active after confirmation. The login
   check for `IsActive` therefore only matters for rows modified outside the domain model.
10. `UserRegistration.Expire()` and the `Expired` status exist (with rules and a domain event), but no caller or scheduled
    job that triggers expiry was found in `Registrations/Infrastructure` (only unit tests call it). Expiry is effectively
    not enforced in the shipped flow.

## 7. Behaviours worth noting before porting

- Single OAuth client, single secret, single grant (ROPC); public clients cannot use it without embedding the secret.
- Errors on bad credentials come from IdentityServer (`invalid_grant` + description) per RFC 6749 section 5.2
  **[not verified in IS4 source]**.
- No logout/revocation endpoint, no refresh, no token introspection use in the repo.
- Login identifier is `Login`; `Email` is a separate column.
- Authorization is data-driven (DB roles -> permission codes), per request, in-process via the module interface.
- `sub` is the only claim the application code depends on (`ExecutionContextAccessor`).
