# Feasibility with Symfony 8.1 SecurityBundle + lexik/jwt-authentication-bundle (+ API Platform)

Facts and options only; no decision. Target stack (from `composer.json` in this repo): PHP >= 8.5, Symfony 8.1.*.
Source flow is documented in `source-auth-flow.md`. **[not verified]** marks claims not confirmed against primary
sources in this run.

## 1. Compatibility facts

- lexik/jwt-authentication-bundle 3.x `composer.json`: `php >=8.2`, `lcobucci/jwt ^5.0`,
  `symfony/security-bundle ^6.4|^7.0|^8.0`, `symfony/config ^6.4|^7.0|^8.0`; dev dep `api-platform/core ^3.0|^4.0`.
  Source: https://raw.githubusercontent.com/lexik/LexikJWTAuthenticationBundle/3.x/composer.json
- Compatibility of lexik's own API Platform integration with API Platform 5.0 (the version the API Platform docs site
  currently labels "current") is **[not verified]**; lexik's dev constraint only lists 3.x/4.x.
- gesdinet/jwt-refresh-token-bundle: PHP >= 8.2, Symfony 6.4, 7.2+ or 8.0+.
  Source: its README (https://github.com/markitosgv/JWTRefreshTokenBundle).
- Symfony 8.1 docs (security/access_token) mention a built-in `oidc` handler needing `web-token/jwt-library`, and
  `enforce_key_usage_verification` "added in 8.1".
  Source: https://symfony.com/doc/current/security/access_token.html

## 2. Capability matrix

| Source behaviour | lexik + SecurityBundle | Status |
|---|---|---|
| Signed JWT access token, RS256, asymmetric key | `encoder.signature_algorithm` default `RS256`; keys via `secret_key`/`public_key`/`pass_phrase`; `lexik:jwt:generate-keypair` | Reproducible |
| Lifetime 3600 s | `token_ttl` default `3600` | Reproducible (same default) |
| Bearer header on requests | `token_extractors.authorization_header` default prefix `Bearer`, name `Authorization`; firewall `jwt: ~` | Reproducible |
| Stateless API | firewall `stateless: true` | Reproducible |
| Custom claims (`name`, `email`, `client_id`, `scope`, `iss`, `aud`) | `JWT_CREATED` event: `getData()`/`setData()`; `createFromPayload($user, $payload)` for manual creation | Reproducible via listener (no config nodes) |
| `sub` = user GUID | `user_id_claim` (default `username`, non-empty) is the payload key for the user identifier | Reproducible with config; see 3.2 for how the value is chosen |
| `iss` / `aud` validation | no `issuer`/`audience` config nodes in `DependencyInjection/Configuration.php`; checks would go in a `JWT_DECODED` listener, or use Symfony `access_token` + `oidc` handler which has `issuers` and `audience` options | Needs custom code or another authenticator |
| `POST /connect/token` form-urlencoded ROPC | not provided. lexik wires `json_login` (JSON body, `username`/`password` by default; `username_path`/`password_path` configurable) + `lexik_jwt_authentication.handler.authentication_success` which returns a JSON body with `token` | Needs custom authenticator/controller for wire compatibility |
| OAuth2 response shape (`access_token`, `token_type`, `expires_in`, `scope`) and errors (`error`, `error_description`) | lexik success handler body is `{"token": ...}`. A custom success handler is the extension point | Needs custom code |
| Client authentication (`client_id` + `client_secret`) | no equivalent | Needs custom code (credential check inside authenticator or a controller) |
| Refresh tokens | not in lexik (index.rst points to JWTRefreshTokenBundle). Source has **no** refresh | Not a port; optional addition (gesdinet bundle) |
| Token revocation/logout | lexik `blocklist_token` (cache-backed, `cache.app`) | Not in source; optional |
| IdentityServer discovery/JWKS endpoints | no `.well-known` endpoints in lexik config tree | Diverges (only matters for external consumers) |
| Password verification (PBKDF2-SHA256, 1000 it., `0x00`+salt+key, Base64) | Symfony custom `PasswordHasherInterface` registered via `password_hashers: <UserClass>: { id: <service> }`, with `migrate_from` / `needsRehash()` to upgrade to a native hasher on login | Needs custom hasher (see 3.4) |
| Per-request DB permission check | Voters on arbitrary attribute strings, `#[IsGranted]`, `is_granted()` in API Platform `security` | Reproducible with a custom voter (see 3.3) |
| Startup check that every endpoint is protected | no built-in equivalent found | Needs custom code (compiler pass, route listing test) |
| Registration + confirmation flow | not an auth-stack feature | Application code (UserAccess/Registrations modules) |

## 3. Details by area

### 3.1 Token issuance (`/connect/token`)

- Symfony `json_login` expects `Content-Type: application/json` with `username`/`password` keys
  (https://symfony.com/doc/current/security.html, section "JSON Login"). The source accepts
  `application/x-www-form-urlencoded`. Options:
  - A. Keep `json_login` + lexik handlers (JSON contract, `token` field). Diverges from the source wire format.
  - B. A custom authenticator extending `AbstractAuthenticator` (`supports()` on `POST /connect/token`,
    `authenticate()` reading `$request->request`, returning `new Passport(new UserBadge($login), new PasswordCredentials($pw))`
    and, if desired, a `CustomCredentials` or controller check for the client id/secret; `onAuthenticationSuccess()`
    builds the OAuth-shaped JSON using `JWTTokenManagerInterface::create($user)`;
    `onAuthenticationFailure()` returns `{"error":"invalid_grant","error_description":...}`).
    Sources: https://symfony.com/doc/current/security/custom_authenticator.html (methods, `Passport`,
    `PasswordCredentials`, `CustomCredentials`, JSON failure response) and lexik doc "7-manual-token-creation" (the
    manager's `create()` dispatches `JWT_CREATED` and `JWT_ENCODED` but not `AUTHENTICATION_SUCCESS`).
  - C. A plain controller on the route that calls a UserAccess authenticate use case and
    `JWTTokenManagerInterface::createFromPayload()`. Bypasses SecurityBundle for login (no passport events,
    no password rehash badge).
- Error text parity: the source returns `"Incorrect login or password"` for both unknown login and wrong password and
  `"User is not active"` for inactive users. These are controlled by application code in any option.
- OAuth ROPC itself: RFC 9700 (OAuth 2.0 Security BCP) section 2.4: "The resource owner password credentials grant
  [RFC6749] MUST NOT be used." (https://datatracker.ietf.org/doc/html/rfc9700). A wire-compatible endpoint keeps
  that property of the source.

### 3.2 Token validation and user identity

- `JWTAuthenticator::authenticate()` extracts the token, calls `jwtManager->parse()`, reads
  `getUserIdClaim()` from the payload (throws `InvalidPayloadException` if missing), casts it to string, and builds a
  `SelfValidatingPassport(new UserBadge($id, loader))`. The loader calls the firewall's user provider
  `loadUserByIdentifier($id)`, or `loadUserByIdentifier($id, $payload)` for providers implementing
  `AttributesBasedUserProviderInterface` (`PayloadAwareUserProviderInterface` is deprecated since 3.3).
  Source: lexik `Security/Authenticator/JWTAuthenticator.php` (3.x).
- Consequence: **a user provider is always involved** on each request. The source never loads the user on a request; it
  only reads `sub`. Options: a provider that queries the UserAccess read model (one query/request), or one that builds
  a lightweight user from the payload (`AttributesBasedUserProviderInterface`). Symfony's access_token doc shows the
  `UserBadge` loader-callable pattern to build the user from the token without a provider (for the `access_token`
  authenticator, not lexik's `jwt`).
- Identifier claim: `JWTManager::create()` builds `['roles' => $user->getRoles()]` and sets
  `$payload[$userIdClaim]` from a readable property named like the claim on the user object, falling back to
  `user_identifier` (`getUserIdentifier()`). So `user_id_claim: sub` yields `sub = getUserIdentifier()` unless the user
  exposes a `sub` property. For `sub` to be the GUID, `getUserIdentifier()` would have to return the GUID (and the
  user provider would load by id), or a `JWT_CREATED` listener overrides it. Login by `Login` then happens in the
  authenticator, separate from the token identity.
- `iat`/`exp` are not in `JWTManager` code read; they are added by the encoder (lcobucci) **[not verified]**.
- Alternative validator: Symfony `access_token` authenticator with the built-in `oidc` handler (requires
  `web-token/jwt-library`; GMP recommended) validates JWS locally with `algorithms`, `keyset`, `audience`,
  `issuers`, and lets a custom `AttributesBasedUserProviderInterface` receive claims. This is an alternative to lexik's
  `jwt` authenticator for validation, but lexik's own `jwt` authenticator and the `oidc` handler are separate
  mechanisms.

### 3.3 Roles and permissions

- Source: permission codes (strings such as `GetMeetingDetails`) resolved from DB per request; roles never appear in
  the token; role codes are `Member` / `Administrator`.
- lexik default payload includes `roles` from `$user->getRoles()`; Symfony expects role strings such as `ROLE_*` for the
  `role_hierarchy` and `access_control` features. Hierarchy docs: do not read `getRoles()` manually, use `isGranted`.
- Voters accept arbitrary attribute strings: "these values are arbitrary strings; you can use anything"
  (https://symfony.com/doc/current/security/voters.html). A `Voter` with `supports()` returning true for the known
  permission codes and `voteOnAttribute()` calling a UserAccess port reproduces `HasPermissionAuthorizationHandler`.
  Voters can implement `supportsAttribute()` to skip unrelated attributes. `#[IsGranted('PermissionCode')]` on controller
  actions, or `security: "is_granted('PermissionCode')"` on API Platform resources/operations, both go through voters
  (https://api-platform.com/docs/symfony/security/; the page is labelled version 5.0).
- Options for where permissions live:
  - A. Per request via a voter calling UserAccess (same semantics as source: immediate effect of role changes).
  - B. Loaded when the user is built by the user provider and exposed as Symfony roles/attributes.
  - C. Embedded in the JWT via `JWT_CREATED` (differs from source; permissions are stale until expiry).
- API Platform notes: `security` runs before denormalization (`object` = stored values);
  `securityPostDenormalize` after (`previous_object` available); voters cannot be used on collection GET; docs say to
  filter collections in a state provider.
- Startup completeness guard (`AuthorizationChecker`): no Symfony built-in found. Options: a compiler pass or test that
  enumerates controllers/API Platform operations and asserts a permission attribute or an explicit public marker; or a
  deny-by-default `access_control` rule (`IS_AUTHENTICATED_FULLY` for everything, `PUBLIC_ACCESS` for the public
  routes) which is documented in the lexik and API Platform docs.
- The `#if !DEBUG return true` behaviour of the source has no counterpart; any port decides explicitly.

### 3.4 Password hashes from the source

- Source format: Base64(`0x00` | salt16 | PBKDF2-HMAC-SHA256(password, salt, 1000, 32 bytes)).
- Symfony: a custom hasher implementing `PasswordHasherInterface` (`hash`, `verify`, `needsRehash`) registered with
  `id:`, enforcing the 4096-char limit; or keep it as a `legacy` hasher referenced through `migrate_from` so that a
  successful login rehashes to the new algorithm. Symfony's page states that PBKDF2 "is no longer recommended".
  Source: https://symfony.com/doc/current/security/passwords.html
- Does not apply if the port generates new hashes and seeds new users (no data migration of the .NET database).

### 3.5 Refresh and lifetime

- Source: access token 3600 s, no refresh token.
- lexik: `token_ttl: 3600` default; `lexik/jwt-authentication-bundle` index.rst says that on expiry requests return 401 and
  re-authentication is needed, pointing to JWTRefreshTokenBundle for refresh.
- gesdinet/jwt-refresh-token-bundle: `refresh_jwt` authenticator with `check_path`, `ttl` default 2592000 s,
  `single_use`, storage via ORM/ODM/DBAL/custom manager, hooks lexik's authentication-success event, can use lexik's
  blocklist to revoke the replaced JWT. Adding it changes the contract (source has no refresh).

### 3.6 Documentation of the API (OpenAPI)

- API Platform docs (JWT page, version 5.0 label) show: `composer require lexik/jwt-authentication-bundle`, firewall with
  `json_login` and `jwt: ~`, `access_control` making the login route public, Swagger security via
  `api_platform.swagger.api_keys` (`name: Authorization`, `type: header`), and lexik's `api_platform:` block
  (`check_path`, `username_path`, `password_path`) that adds a token retrieval endpoint to Swagger UI. The page also
  recommends open standards such as OpenID Connect for production.
  Source: https://api-platform.com/docs/symfony/jwt/
- That lexik `api_platform` block assumes a JSON login body; a custom form-urlencoded endpoint would need its own OpenAPI
  description **[not verified]**.

## 4. Divergence summary

1. Token endpoint: JSON `json_login` + `{"token": ...}` (lexik default) vs form-urlencoded `/connect/token` OAuth2 response.
   Wire compatibility requires a custom authenticator/controller.
2. No OAuth client concept (client id/secret) in lexik.
3. User provider is mandatory in lexik's `jwt` authenticator (source never loads the user per request).
4. Default payload claim `roles` (Symfony role strings) vs source tokens that carry no roles; claims `iss`/`aud`/`scope`/
   `client_id` need listeners.
5. Permissions: voter-based in Symfony; source uses policy+attribute handler. Behavioural parity needs a UserAccess port
   called per request.
6. Refresh/revocation/discovery are not in the source and not in lexik (refresh and blocklist are optional add-ons).
7. Source defects/quirks that a port either reproduces or drops: no `roles` claim despite plumbing; role constant
   mismatch (`Admin`/`User` vs `Member`/`Administrator`); `#if !DEBUG` permits everything; confirmation credential is
   the registration GUID; registration expiry never triggered; committed `tempkey.rsa`; hard-coded authority URL.
8. Keys: IdentityServer developer signing key vs lexik PEM keypair (`lexik:jwt:generate-keypair`); existing tokens are not
   portable (no need if tokens are short-lived).

## 5. Open points not resolved by this research

- IS4-side details not read from library source: exact token response body, `aud`/`iss` values, discovery/JWKS use by
  consumers, whether `UseIdentityServer()` adds the authentication middleware.
- lexik: where `iat`/`exp` are added (encoder), exact behaviour of `JWT_CREATED` ordering with `createFromPayload`.
- API Platform 5.0 vs lexik 3.x integration (`api_platform:` block) not tested.
- Whether other modules react to the user-created integration events (Meetings member creation) was not examined; the
  seed inserts `meetings.Members` directly.

## 6. Primary sources used

- .NET source files listed in `source-auth-flow.md`.
- IdentityServer4 4.1.2: `Client.cs` and `DefaultClaimsService.cs`, `TokenRequestValidator.cs` (GitHub raw, tag 4.1.2);
  Duende docs (successor) for refresh and token endpoint.
- lexik 3.x: `composer.json`, `DependencyInjection/Configuration.php`, `Security/Authenticator/JWTAuthenticator.php`,
  `Services/JWTManager.php`, docs `index.rst`, `1-configuration-reference`, `2-data-customization`,
  `6-extending-jwt-authenticator`, `7-manual-token-creation` (via context7 `/lexik/lexikjwtauthenticationbundle`).
- Symfony 8.1 docs: security.html, security/custom_authenticator.html, security/access_token.html,
  security/voters.html, security/passwords.html.
- API Platform docs: /docs/symfony/jwt/, /docs/symfony/security/.
- gesdinet/jwt-refresh-token-bundle README; RFC 9700 section 2.4.
