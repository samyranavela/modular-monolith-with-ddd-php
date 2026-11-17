# Profile management

A user gives an email address and a password when registering. Profile management is how they change them afterwards, and how they get back in after forgetting the password. Both belong to [Accounts](description.md), which holds the account.

Terms are defined in the [Accounts glossary](../../../src/Accounts/GLOSSARY.md). The messages sent are listed in [the catalogue of Notifications](../notifications/catalogue.md#accounts-profile).

The **login** and the **names** are not covered: nothing lets a user change them, and no use case needs it yet.

## Who takes part

- A **user**, member or administrator, changes the email address and the password of their own account. Nobody changes them for someone else, administrators included.
- **Anyone who knows an email address** can ask for a password reset, signed in or not. The answer never says whether the email address is known.
- [Notifications](../notifications/description.md) sends the messages. They are [required messages](../notifications/description.md#required-messages): they go to an email address, by email only, and nobody can switch them off.

## Changing the email address

A signed-in user gives their **current password** and the **new email address**. If the new email address can be used, this creates an **email address change request**, which waits for the new email address to be confirmed by a link.

- **Refused** when the password is wrong, when the account is [locked](account-deletion.md#locked-account), and when the new email address is the current one.
- **Neutral answer.** Whatever the situation of the new email address, the user reads the same sentence: a link is sent if the email address can be used. An authenticated user could otherwise probe which email addresses are in use.

| Situation of the new email address | What happens behind the answer |
| :-- | :-- |
| Free | A request is created, and a confirmation link goes to the new email address. |
| Held by an account, confirmed or pending | Nothing is created, and nothing is sent to the holder: the account is left as it was. |

In every case the **current email address** is told that a change was requested, with the new email address and the date, so that a request the user did not make does not go unnoticed. The message holds no link and no secret.

- **Until it is confirmed**, the current email address stays the account's: notifications, password resets and every message go to it. The new email address receives nothing but the link.
- **One request at a time.** An account has one email address change request at most. A new request replaces the previous one, whose link stops working, and the current email address is told again.
- **Confirming.** Following the link confirms the request, signed in or not: the link proves that the email address is the person's. It works **once** and **before its deadline**, 48 hours after it was sent, configurable. The email address is checked **again** at that moment: if another account has taken it since, the link is refused, as if it were expired or invalid, without saying which.
- **Once confirmed**, the new email address is the account's at once and the request is gone. The **old email address is free again**, and the user's sessions go on. A message tells the old email address that it changed, and to which one.
- **Expiring.** After its deadline a request can no longer be confirmed. Removing it is housekeeping, not the rule.
- **Account deletion.** When the user requests the deletion of their account, the request in progress, if any, is dropped. The email address is freed with the rest when the account is deleted.

An email address belongs to a single account, pending or confirmed. The rule is absolute: when two requests for the same email address are confirmed at the same moment, or one of them and a registration with that email address, only one succeeds and the others are refused.

## Changing the password

A signed-in user gives their **current password** and a **new password**.

- **Refused** when the current password is wrong, or when the new one breaks the [rules on a password](#rules-on-a-password). A locked account may change its password: it restores nothing it did not have, and a user who suspects someone else must be able to.
- **Once changed**, the user's **other sessions end** and the current one goes on. A message tells the current email address that the password changed and when, and what to do if the user did not do it.

## Forgotten password

Anyone gives an email address. The answer is the same sentence whatever the situation, and arrives with the same delay: a link is sent if an account exists.

| Situation of the email address | What happens behind the answer |
| :-- | :-- |
| Held by a confirmed account | A **password reset request** is created, replacing the previous one, and a link goes to the email address. |
| Held by no account, or by a pending account | Nothing happens, and nothing is sent: nobody is written to for an email address that is not a confirmed account's. |

- **The link** works **once**, **1 hour** after it was sent, configurable. A new request replaces the previous one, whose link stops working. A used or expired link is refused with a message that says which.
- **Resetting.** Following the link, the person chooses a new password, which follows the [rules](#rules-on-a-password). The request is gone, **every session of the user ends**, and a message tells the email address that the password changed.
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
| An email address change is requested | The current email address | Email address change requested |
| An email address change is requested to a free email address | The new email address, with the link | Email address change confirmation |
| An email address change is confirmed | The old email address | Email address changed |
| A password reset is requested for the email address of a confirmed account | That email address, with the link | Password reset link |
| The password is changed or reset | The current email address | Password changed |

An administrator has no inbox and is never notified, but these messages go to the email address of any account, an administrator's included: they are security messages, and nobody can turn them off.

## Permissions

Accounts defines two permissions, both of which **change** something: **changing one's own email address** and **changing one's own password**. Accounts grants them to the Member and Administrator roles. Asking for a reset and following a link need no permission, since the person is not signed in, as for [registering](registration.md#registering).

Applying the lock, Accounts refuses a locked account every permission that changes something, except those listed in [the description](description.md#permissions), which include changing the password.

## What Accounts needs from other contexts

Nothing. The email address of every account, pending or confirmed, is held here, so the rules on email addresses are checked inside Accounts.

[Notifications](../notifications/description.md#what-notifications-needs-from-other-contexts) needs the current email address of the account, which is the old one until a change is confirmed.

## Delays and limits

- A link to confirm an email address change is valid for 48 hours, and a reset link for 1 hour. Both are configurable.
- The shortest password is 15 characters, configurable.

## What is out of scope for now

- Changing the **login** and the **names**.
- A **second factor**, and a choice, at a password change, of which other sessions to end.
- A link in the message to the old email address to **cancel** an email address change.
- A **history** of the email addresses an account had.

See the [conceptual model](profile-management-conceptual-model.md) and the [event storming](profile-management-event-storming.md).
