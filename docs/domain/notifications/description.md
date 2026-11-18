# Notifications

Notifications is how the application reaches a person about something that happened. It keeps, for each member, a list of **notifications** they can read and mark as read, and it **delivers** them by email, and later by other channels.

Terms are defined in the [Notifications glossary](../../../src/Notifications/GLOSSARY.md). The kinds are listed in [the catalogue](catalogue.md); see also the [conceptual model](conceptual-model.md) and the [event storming](event-storming.md).

The other contexts decide *that* someone must be notified and *who*. Notifications decides everything else: how the notification is worded, through which channels it goes, and how the member reads it afterwards. It knows nothing of the rules of the other contexts.

## Who takes part

- A **member** is the person who is notified, and the only one who reads their own notifications. Nobody reads the notifications of someone else.
- An **administrator** is not an ordinary user: they read everything their role allows, but they are never notified and have no inbox. The only emails they receive are [required messages](#required-messages): the confirmation of their email address when they are invited, before their account is confirmed, and the security messages of [profile management](../accounts/profile-management/description.md#messages), such as a password reset link.
- An **email address** is where a [required message](#required-messages) goes when there is no member: a person whose account is not confirmed yet, or a member whose account has just been erased.
- The **other contexts** are the ones that have something to report. Each hands Notifications who to notify, what happened and the data that goes with it.

## Notification and delivery

A **notification** is what happened, seen by one member. It has a **kind**, the **data** of the fact, the moment it was created, and whether the member has **read** it. A single fact reaching several members gives one notification for each.

A **delivery** is the sending of a notification through one **channel** other than the inbox, which needs no delivery. It has its channel, the moment it was sent and whether it succeeded.

| Channel | State |
| :-- | :-- |
| **Inbox** | Every notification is in the inbox of its member, always. |
| **Email** | The notification is also sent to the email address of the member, according to its kind. |
| **Push** | Planned for a mobile application, not part of the current scope. |

Because the inbox is always there, a member who receives a single email can find every notification they were sent in one list, whichever channels carried it. A failed delivery never removes a notification: it stays in the inbox.

Whether a notification was **read** is a fact of the inbox only. The application never learns whether an email was opened. For a channel that reports it, such as a push, the delivery could say so later, but the notification is read only when the member reads it in the application.

## The catalogue

Every notification belongs to a **kind**, and the kinds are listed in the **catalogue**. A kind says:

- **who** it is for, among the members of the fact,
- **what data** it needs, such as the meeting, the reason of a removal or a date,
- for each channel it uses, a **template**: a title and a text, short for the inbox, longer for an email.

The catalogue is the only thing the other contexts know of Notifications. A new case in a context that must notify adds a kind to the catalogue, with its templates. The catalogue is part of the application: there is no screen to edit it. The kinds are listed by context in [the catalogue](catalogue.md).

The text is written from the data and the current template when the member reads or receives it. The data is kept with the notification, so the words can change without losing what happened. The data is a snapshot of the moment of the fact: a group renamed later does not change an old notification.

## Required messages

Some emails have no member to belong to, or cannot be left to a member's choice: the confirmation link that proves an email address, for a registration as for the invitation of an administrator, the notice that an account was deleted, and the messages of [profile management](../accounts/profile-management/description.md#messages): the confirmation of a new email address, the notice to the old email address, the password reset link and the notice of a password change. They are **required messages**.

A required message goes to an email address, by email only. It has no inbox, nothing to read and no preference that could turn it off. It is in the catalogue like any other kind, listed apart.

## The inbox

A member, for themselves:

- **lists** their notifications, the most recent first, each with its title, its text, its date and whether it is read,
- **marks** one notification as read, or all of them,
- **counts** the notifications they have not read.

A member whose [account is locked](../accounts/account-deletion/description.md#locked-account) still reads their inbox and marks notifications as read.

A notification stays in the inbox once read. The member cannot delete one, and how long they are kept is left for later.

## One fact, one notification

When a context hands Notifications a fact, each member concerned must get **one notification, never lost and never twice**, even when the same fact is handed twice. A required message is identified in the same way, by its fact and the email address: a confirmation link sent again, with a new link, is a new fact.

A delivery is attempted promptly, and a failure of a channel is repeated, within limits left to the implementation.

Notifications refuses a fact whose kind is not in the catalogue, or that lacks the data of its kind. It never decides alone to notify: a notification only exists because a context handed it a fact.

## Export of the data

The notifications of a member are part of the [export of their data](../accounts/account-deletion/description.md#exporting-the-data): for each one, its kind, its date and whether it is read.

## When an account is deleted

The notifications of a deleted member go with the account: their inbox is deleted. The required message that confirms the deletion goes to the email address that the deletion takes away: the email address comes with the fact, so the message needs no member. See [account deletion](../accounts/account-deletion/description.md).

## What Notifications needs from other contexts

This page and the following ones describe the information a context needs from another, and how exact or fresh it must be. They never say how the information is obtained: whether it is requested at the moment it is needed or kept up to date beforehand is a technical choice, made in the implementation.

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| The email address of a member | [Accounts](../accounts/description.md) | To send the email of a notification | Exact when the email is sent |
| That a member's account was deleted | [Accounts](../accounts/account-deletion/description.md) | To delete their inbox | Exact: no notification exists for a deleted member, even when a deletion and a creation happen at the same moment |
| The access of an account: its role and its state | [Accounts](../accounts/description.md#access) | To allow or refuse each reading and each marking as read | Exact at every action, read at that moment and never kept |

## What the other contexts give to Notifications

A context that wants someone notified gives Notifications:

| Information | Why | Exactness |
| :-- | :-- | :-- |
| The **member** to notify, or the **email address** for a required message | To know whom it is for | Exact. One member for each notification |
| The **kind** | To find the templates and the channels | A kind of the catalogue |
| The **data** of the fact, and when it happened | To fill the templates | As the kind lists it, complete from the start |

A context that has several members to notify, such as every attendee of a cancelled meeting, hands one fact for each of them.

## Who may do what

Notifications allows two kinds of action to the Member role: **reading one's own notifications**, which covers the list and the count, and **marking one's own notifications as read**. Marking as read is the one change a locked member may still make here.

## What is out of scope for now

- **Preferences**: turning a kind or a channel on or off for oneself. The catalogue, with its kinds and channels, is what they will build on. Required messages will never be concerned.
- **Digests**: grouping several notifications in one email.
- **Push**: the channel is planned for a mobile application that is not part of the scope.
- **Editing the templates** from the application, and several languages.
- **Deleting notifications**, and how long they are kept.
