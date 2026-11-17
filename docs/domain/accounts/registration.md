# Registration

Registering is how a person gets an account. It creates a **pending account**, which stays so until a link proves that the email address given at the start really belongs to the person, and the person has filled in the rest.

Terms are defined in the [Accounts glossary](../../../src/Accounts/GLOSSARY.md). What an account is, and the states it goes through, is in the [description of Accounts](description.md).

A pending account is created in one of two ways, and both end the same way, with a confirmation:

- a person **registers** themselves and becomes a member,
- an administrator is **invited** and becomes an administrator.

In both, only an **email address** is given at the start. The person gives the rest when they follow the link. The role of the account is decided when it is created, and kept for its whole life.

## Registering

A person registers by giving an **email address**. This creates a pending account with the Member role, and sends a **confirmation link** to the email address, as a [required message](../notifications/description.md#required-messages).

The answer is the **same sentence whatever the situation**, so that it cannot be used to discover which email addresses have an account: a link is sent if the email address can be used.

| Situation of the email address | What happens behind the answer |
| :-- | :-- |
| Free | A pending account is created and a link is sent. |
| Held by a pending account | A new link is sent and the 48 hours start again. The account itself is left as it was, so nobody can take over someone else's account. |
| Held by a confirmed account | Nothing happens, and nothing is sent. Whoever already has an account can reset their password. |

An email address belongs to a single account, whatever its state: a pending account holds its email address as a confirmed one does. The rule is absolute. When two registrations reach the same email address at the same moment, or when a registration and the [confirmation of a change of email address](profile-management.md#changing-the-email-address) reach it at the same moment, only one succeeds and the other is refused.

## Inviting an administrator

An existing administrator, or whoever operates the application, invites someone by giving only their **email address**. This creates a pending account with the Administrator role, and sends a confirmation link to the email address, as a [required message](../notifications/description.md#required-messages). The invitation is the only way to have an administrator: the first one is invited by whoever operates the application, since no administrator exists yet to do it.

An invitation is refused when the email address already belongs to an account, pending or confirmed. Whoever invites is an authenticated, trusted person, so the refusal can say so plainly, unlike the neutral answer given to someone registering themselves.

## Confirming

Following the link shows the person a form, and the account is confirmed when they **validate** it. They give:

- a **login**,
- a **first name** and a **last name**,
- a **password**, twice, which follows the [rules on a password](profile-management.md#rules-on-a-password).

Nobody but them ever knows their password, and the email address is proven to be theirs by following the link.

A login belongs to a single account. The rule applies at that moment: if the chosen login is taken, the person chooses another one and the account stays as it was. It is absolute: when two people validate the same login at the same moment, only one succeeds.

Validating takes the account from **pending** to **confirmed**. An account can be confirmed once, and only before it expires. The link works until the deadline, **or until the account is confirmed**: it is gone with the confirmation. A person who followed it and left the form can come back to it, as long as the 48 hours are not over.

A pending account can do nothing, not even sign in. Once it is confirmed, it can do what its role grants.

A confirmed account is the signal that the person is real. The other contexts need to know that an account was confirmed, and with which role, and each creates what it needs for that person:

- **Member role**: a member of the meeting groups, in [Meetings](../meetings/description.md), and a payer, in [Payments](../payments/description.md).
- **Administrator role**: nothing else. An administrator is not a member and not a payer.

Nothing of that exists before the confirmation. Each confirmation must give exactly one member and one payer for the Member role: never lost and never twice. How each context learns of it is a technical choice, made in the implementation.

## Expiring

A pending account waits for **48 hours**, whether it comes from a registration or from an invitation. After that, it can no longer be confirmed, and it is removed, which frees its email address for someone else.

Removing is housekeeping, not the rule: from the moment the 48 hours are over, the link is refused, even if the account has not been removed yet. Whoever follows an old or used link is told that it is expired or invalid, without knowing which: a removed account and a made-up link look the same.

## When the account is deleted

When a confirmed account is [deleted](account-deletion.md), its login and its email address are free again, as after an expiry.

## What is out of scope for now

- Registering with an **external identity** (a social sign-in with another provider).

See the [conceptual model](registration-conceptual-model.md) and the [event storming](registration-event-storming.md) of the registration.
