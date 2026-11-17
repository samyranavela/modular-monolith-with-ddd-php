# Notifications

Reaches members about what happened in the application: it keeps for each member a list of notifications they can read, and delivers them by email, and later by other channels.

## Language

**Notification**:
What happened, seen by one member: its kind, the data of the fact, when it was created, and whether the member has read it. A fact reaching several members gives one notification for each, and a fact handed twice gives only one.
_Avoid_: Alert, message, event

**Notification Kind**:
The sort of fact a notification is about, such as a meeting that is cancelled. It says whom it is for, the data it needs and, for each channel it uses, the template of its text. A notification is always of a kind of the catalogue.
_Avoid_: Notification type, rule, category

**Catalogue**:
The list of all the kinds of notification, with their templates. It is part of the application, and it is the only thing the other contexts know of Notifications.
_Avoid_: Registry, rule book

**Template**:
The title and the text of a kind for one channel: short for the inbox, longer for an email. It is filled with the data of the notification.
_Avoid_: Layout, content

**Channel**:
A way a notification reaches a member: the inbox, the email, and later the push.
_Avoid_: Medium, transport

**Inbox**:
The list of the notifications of a member, which they read, mark as read and count. Every notification is in it, whichever other channels carry it.
_Avoid_: Mailbox, feed

**Read**:
The state of a notification that the member has read in the application, and the only thing the inbox records of what the member did with it. It does not say that an email was opened.
_Avoid_: Seen, opened

**Delivery**:
The sending of a notification through one channel other than the inbox: when it was sent and whether it succeeded. A failed delivery never removes the notification.
_Avoid_: Dispatch, send

**Required Message**:
An email that goes to an email address and not to a member, and that cannot be left to the member's choice: the confirmation of an email address, the notice that an account was deleted, and the security messages of a change of email address or password. It has no inbox and nothing to read.
_Avoid_: System email, transactional email

**Email Address**:
The email address an email is sent to. For a notification it is the current email address of the member, known to Accounts; for a required message it comes with the fact.
_Avoid_: Recipient

**Fact**:
What a context hands to Notifications to have someone notified: the kind, the member or the email address, the data, and an identity that makes handing it twice harmless.
_Avoid_: Request, trigger, event
