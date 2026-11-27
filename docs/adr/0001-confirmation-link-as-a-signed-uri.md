---
status: accepted
date: 2026-11-27
---

# Sign the confirmation link instead of storing a token

## Context and Problem Statement

Accounts sends a confirmation link to the email address of a pending account (see the [registration](../domain/accounts/registration/description.md)). The link must prove that the person owns the address, work until the confirmation deadline (48 hours), die when the account is confirmed, and be replaced by a new link when one is sent again for the same pending account. How is the link made, without a secret in the domain and without persistence, which is not wired yet?

## Decision Drivers

* The domain holds no secret and no storage detail.
* Persistence is not wired yet, and a mechanism is built when a use case needs it.
* The link carries something that proves the email address, which an identifier alone does not.
* The project is on Symfony 8.1, and 8.2 is not stable yet (only `8.2.0-RC1`).

## Considered Options

* A signed URI (Symfony `UriSigner`)
* A random token stored with the account
* The `AccountId` as the secret

## Decision Outcome

Chosen option: "A signed URI", because it needs no storage and no secret in the domain, and works on Symfony 8.1.

The link carries the `AccountId` and is signed with the confirmation deadline as its expiration. Three rules follow:

* **One use.** The domain guarantees it: confirming an already confirmed account is refused (`AccountAlreadyConfirmed`), and the form is shown only for a pending account. Whoever follows a used link is told that it is expired or invalid, without knowing which.
* **One valid link at a time.** A link sent again carries the new deadline as its expiration, and a link whose expiration is not the account's current deadline is refused.
* **Building the link is infrastructure.** The fact handed to Notifications carries the `AccountId`, the email address and the confirmation deadline. Signing, with the route of the confirmation, is not part of the domain.

### Consequences

* Good, because nothing is stored and the same mechanism serves the other links of Accounts (change of email address, password reset).
* Good, because `Account` gets an `AccountId`, its identity: other contexts and the facts of the Contract refer to it, never to the email address, which can change.
* Bad, because the signature alone does not make the link single-use before Symfony 8.2: the domain does.

### Confirmation

Unit tests on `Account` (deadline, already confirmed, renewed link) and application tests on the use cases of the registration. The signing itself is tested when its adapter exists.

## Pros and Cons of the Options

### A signed URI (Symfony `UriSigner`)

* Good, because no table, no generator port and no secret in the domain.
* Good, because the expiration is part of the signature.
* Neutral, because the previous link is refused by comparing its expiration with the account's deadline, not by the signature.
* Bad, because single use depends on the domain until `$version` is available.

### A random token stored with the account

* Good, because it works with any version and kills a link by deleting its token.
* Bad, because the domain would carry a secret, a generator port and a storage kept in step with the deadline and the confirmation.
* Bad, because it needs persistence, which is not wired yet.

### The `AccountId` as the secret

* Good, because nothing else is needed.
* Bad, because an id appears in logs and events, and a link anyone could build from it would prove nothing about the email address.

## More Information

Symfony 8.2 adds a `$version` argument to `UriSigner`, a value added to the signature: with the account status and the deadline as its version, a link dies by itself on confirmation or when replaced. See [Single-use and safer signed URLs](https://symfony.com/blog/new-in-symfony-8-2-single-use-and-safer-signed-urls). The decision does not rely on it. It is adopted when 8.2 is stable, in #90 (upgrade the project to 8.2) then #91 (bind the link to the account status), and this ADR is amended then.
