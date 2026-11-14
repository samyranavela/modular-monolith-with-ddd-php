# Registrations

Registrations is where a person becomes a user. Its single job is to make sure the address given at the start really belongs to the person, before any account exists.

Terms are defined in the [Registrations glossary](../../../src/Registrations/GLOSSARY.md). What comes after a confirmed registration is in [UserAccess](../useraccess/description.md).

A registration starts in one of two ways, and both end the same way, with a confirmation:

- a person **registers** themselves and becomes a member,
- an administrator is **invited** and becomes an administrator.

The role the person will receive is decided when the registration is created, and kept until it is confirmed.

## Registering

A person registers by giving a **login**, a **password**, an **email address**, a **first name** and a **last name**. This creates a **User Registration** for the Member role, which waits for confirmation, and sends a **confirmation link** to the address.

Two rules protect the identifiers:

- A login belongs to a single registration.
- An email address belongs to a single registration.

## Inviting an administrator

An existing administrator, or whoever operates the application, invites someone by giving only their **email address**. This creates a User Registration for the Administrator role, and sends a confirmation link to the address. The invitation is the only way to have an administrator: the first one is invited by whoever operates the application, since no administrator exists yet to do it.

An invitation is refused when the address is already used, by a user or by a registration waiting for confirmation. Whoever invites is an authenticated, trusted person, so the refusal can say so plainly, unlike the neutral answer given to someone registering themselves.

The invited person fills in their login, password, first name and last name when following the link. Nobody but them ever knows their password, and the address is proven to be theirs by the same step.

The login rule applies at that moment: if the chosen login is taken, the person chooses another one and the registration stays as it was.

## Confirming

Following the link confirms the registration. A registration can be confirmed once, and only before it expires.

A confirmed registration is the signal that the person is real. The rest of the system then creates what it needs for that person, according to the role of the registration:

- **Member role**: a [user](../useraccess/description.md), a member of the meeting groups and a payer.
- **Administrator role**: a user only. An administrator is not a member and not a payer.

Nothing of that exists before the confirmation.

## Expiring

A registration waits for **48 hours**, whether it is a registration or an invitation. After that, it can no longer be confirmed, and it is deleted, which frees its login and its email address for someone else.

Deleting is housekeeping, not the rule: from the moment the 48 hours are over, the link is refused, even if the registration has not been deleted yet. Whoever follows an old link is told that it is expired or invalid, without knowing which: a deleted registration and a made-up link look the same.

## Registering again with the same email

Someone who never received the email, or lost it, registers again with the same address. The system answers the same neutral sentence whatever the situation, so the answer cannot be used to discover which addresses are registered:

| Situation of the address | What happens behind the answer |
| :-- | :-- |
| Unknown | A new registration is created and a link is sent. |
| Registration waiting for confirmation | A new link is sent and the 48 hours start again. The registration itself is left as it was, so nobody can take over someone else's sign-up. |
| Already a user | An email tells the owner that an account already exists. Nothing is created. |

See the [conceptual model](conceptual-model.md) and the [event storming](event-storming.md) of the registration.
