# Auth research (issue #5)

Question: document the source authentication flow (IdentityServer4, `/connect/token`, claims, lifetime, refresh,
UserAccess permissions) and determine what is reproducible with SecurityBundle + lexik/jwt-authentication-bundle,
what needs a custom authenticator, and what diverges.

- `source-auth-flow.md`: what the .NET source does, with file references.
- `symfony-feasibility.md`: capability matrix, options per area, divergences, open points.

Key facts (details and sources in the files):

1. Source: embedded IdentityServer4 4.1.2, single ROPC client `ro.client`, form-urlencoded `/connect/token`, access token
   JWT 3600 s (default), no refresh token, claims `sub`/`name`/`email` (+ standard), no roles claim issued.
2. Source authorization: `[HasPermission(code)]` -> DB lookup of permission codes per request via UserAccess
   (`users.v_UserPermissions`); in non-DEBUG builds every check passes.
3. Source registration: anonymous register -> email with registration GUID link -> anonymous confirm -> UserAccess user
   created with role `Member`; expiry exists in the domain but is never triggered.
4. lexik + SecurityBundle reproduce: RS256 JWT, 3600 s TTL, Bearer header, stateless firewall, custom claims via
   `JWT_CREATED`, permission checks via voters.
5. Custom code needed: form-urlencoded OAuth-shaped token endpoint and client credentials, legacy PBKDF2 password hasher,
   user provider/identity mapping (`sub`), `iss`/`aud` handling, endpoint-protection startup guard. Refresh is an optional
   add-on (gesdinet bundle), not part of the source.
