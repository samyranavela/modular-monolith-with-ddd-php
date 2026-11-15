# UserAccess: profile management

A user gives an email address and a password when registering. Profile management is how they change them afterwards, and how they get back in after forgetting the password. Both belong to [UserAccess](description.md), which holds the user.

Terms are defined in the [UserAccess glossary](../../../src/UserAccess/GLOSSARY.md). The messages sent are listed in [the catalogue of Notifications](../notifications/catalogue.md#useraccess-profile).

The **login** and the **names** are not covered: nothing lets a user change them, and no use case needs it yet.

## Who takes part

- A **user**, member or administrator, changes their own address and their own password. Nobody changes them for someone else, administrators included.
- **Anyone who knows an address** can ask for a password reset, signed in or not. The answer never says whether the address is known.
- [Notifications](../notifications/description.md) sends the messages. They are [required messages](../notifications/description.md#required-messages): they go to an address, by email only, and nobody can switch them off.

## Changing the email address

A signed-in user gives their **current password** and the **new address**. This creates an **address change request**, which waits for the new address to be confirmed by a link.

- **Refused** when the password is wrong, when the account is [locked](account-deletion.md#locked-account), and when the new address is the current one.
- **Neutral answer.** Whatever the situation of the new address, the user reads the same sentence: a link is sent if the address can be used. An authenticated user could otherwise probe which addresses are registered.

| Situation of the new address | What happens behind the answer |
| :-- | :-- |
| Free | A request is created, and a confirmation link goes to the new address. |
| Address of a user | Nothing is created. A message tells the owner of the address that an account already exists. |
| Address of a registration waiting for confirmation | Nothing is created, and nothing more is sent: the registration is left as it was. |

In every case the **current address** is told that a change was requested, with the new address and the date, so that the answer cannot be used to tell the three situations apart. The message holds no link and no secret.

- **Until it is confirmed**, the current address stays the user's address: notifications, password resets and every message go to it. The new address receives nothing but the link.
- **One request at a time.** A user has one pending request at most. A new request replaces the previous one, whose link stops working, and the current address is told again.
- **Confirming.** Following the link confirms the request, signed in or not: the link proves that the address is the person's. It works **once** and **before its deadline**, 48 hours after it was sent, configurable. The address is checked **again** at that moment: if a user or a waiting registration has taken it since, the link is refused, as if it were expired or invalid, without saying which.
- **Once confirmed**, the new address is the user's address at once. The **old address is free again**, and the user's sessions go on. A message tells the old address that the address changed, and to which one.
- **Expiring.** After its deadline a request can no longer be confirmed. Deleting it is housekeeping, not the rule.
- **Account deletion.** When the user requests the deletion of their account, a pending request is cancelled. The address is freed with the rest when the account is erased.

An address belongs to a single user or a single waiting registration. The rule is absolute: when two requests for the same address are confirmed at the same moment, or one of them and the registration with that address, only one succeeds and the others are refused.

## Changing the password

A signed-in user gives their **current password** and a **new password**.

- **Refused** when the current password is wrong, or when the new one breaks the [rules on a password](#rules-on-a-password). A locked account may change its password: it restores nothing it did not have, and a user who suspects someone else must be able to.
- **Once changed**, the user's **other sessions end** and the current one goes on. A message tells the current address that the password changed and when, and what to do if the user did not do it.

## Forgotten password

Anyone gives an address. The answer is the same sentence whatever the situation, and arrives with the same delay: a link is sent if an account exists.

| Situation of the address | What happens behind the answer |
| :-- | :-- |
| Address of a user | A **password reset request** is created, replacing the pending one, and a link goes to the address. |
| No user, no waiting registration | A message tells the owner of the address that no account exists for it. |
| Address of a registration waiting for confirmation | Nothing more is sent. |

- **The link** works **once**, **1 hour** after it was sent, configurable. A new request replaces the previous one, whose link stops working. A used or expired link is refused with a message that says which.
- **Resetting.** Following the link, the person chooses a new password, which follows the [rules](#rules-on-a-password). The request is used, **every session of the user ends**, and a message tells the address that the password changed.
- **A locked account** can reset its password, and so can an administrator.

## Rules on a password

- At least **15 characters**, configurable, since the application has no second factor.
- At most 128 characters, and a password of up to 64 is always accepted.
- A password found in a list of commonly used passwords is refused.
- No composition rule, such as a required digit or symbol.
- No periodic change and no history: the new password may be one the user had before.

## Messages

| When | Who is told | Kind |
| :-- | :-- | :-- |
| An address change is requested | The current address | Address change requested |
| An address change is requested to a free address | The new address, with the link | Address change confirmation |
| An address change is requested to the address of a user | The owner of that address | Account already exists |
| An address change is confirmed | The old address | Address changed |
| A password reset is requested for the address of a user | That address, with the link | Password reset link |
| A password reset is requested for an address with no user | That address | No account for this address |
| The password is changed or reset | The current address | Password changed |

An administrator has no inbox and is never notified, but these messages go to the address of any user, an administrator included: they are security messages, and nobody can turn them off.

## Permissions

UserAccess defines two permissions, both of which **change** something: **changing one's own email address** and **changing one's own password**. UserAccess grants them to the Member and Administrator roles. Asking for a reset and following a link need no permission, since the person is not signed in, as for [registering](../registrations/description.md#registering).

Applying the lock, UserAccess refuses a locked user every permission that changes something, except those listed in [the description](description.md#permissions), which include changing the password.

## What UserAccess needs from other contexts

This page describes the information a context needs from another, and how exact or fresh it must be. It never says how the information is obtained.

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| Whether an address belongs to a registration waiting for confirmation | [Registrations](../registrations/description.md) | To answer a change of address, and to check the address again at the confirmation | Exact at the request and at the confirmation. Absolute against a registration confirmed at the same moment |

[Registrations](../registrations/description.md#registering-again-with-the-same-email) already needs to know whether an address belongs to a user. [Notifications](../notifications/description.md#what-notifications-needs-from-other-contexts) needs the current address of the user, which is the old one until a change is confirmed.

## Delays and limits

- A link to confirm an address change is valid for 48 hours, and a reset link for 1 hour. Both are configurable.
- The shortest password is 15 characters, configurable.

## What is out of scope for now

- Changing the **login** and the **names**.
- A **second factor**, and a choice, at a password change, of which other sessions to end.
- A link in the message to the old address to **cancel** an address change.
- A **history** of the addresses a user had.

See the [conceptual model](profile-management-conceptual-model.md) and the [event storming](profile-management-event-storming.md).
