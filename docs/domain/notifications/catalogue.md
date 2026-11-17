# The catalogue of Notifications

The catalogue lists every **kind** of notification the application sends, grouped by the context that has something to report. A kind says whom it is for, what data it needs, and which channels carry it, see [the catalogue](description.md#the-catalogue). Terms are defined in the [Notifications glossary](../../../src/Notifications/GLOSSARY.md).

The names are descriptive: the identifier of each kind is a matter for the implementation. The text of the templates is not written here, only the data it needs.

Unless a table says otherwise, every kind goes to the **inbox and by email**. A [required message](description.md#required-messages) is by email only, to an email address. An administrator is never notified: the only emails that reach one are required messages, which go to an email address.

When something is cancelled or removed for a **reason**, the reason is part of the data and shown to the member.

## Accounts

| Kind | For | Data |
| :-- | :-- | :-- |
| **Email address confirmation** | *Required message.* The email address being registered, or the email address of an invited administrator | The confirmation link, when it expires, and the role it leads to. Sent at the registration, at the invitation, and again with a new link when someone registers once more with an email address that waits for its confirmation |
| **Deletion requested** | The member | The date the deletion will be carried out, what will happen, the groups to be archived |
| **Deletion cancelled** | The member | Nothing but the fact |
| **Deletion ended by open items** | The member | The open items that stood in the way |
| **Export ready** | The member | Until when the export can be downloaded |
| **Deletion carried out** | *Required message.* The email address erased by the deletion | The date of the deletion |

Nothing is sent to an email address held by a confirmed account when someone registers with it, nor to an email address that has no account when a password reset is asked for: the answer shown is the same whatever the situation. See [registering](../accounts/registration.md#registering) and [account deletion](../accounts/account-deletion.md#notifications). An administrator who is deleted is not told.

## Accounts: profile

Required messages, to an email address, see [profile management](../accounts/profile-management.md#messages).

| Kind | For | Data |
| :-- | :-- | :-- |
| **Email address change requested** | The current email address of the account | The new email address and the date. No link and no secret. Sent whatever the situation of the new email address |
| **Email address change confirmation** | The new email address | The confirmation link and when it expires |
| **Email address changed** | The old email address | The new email address and the date |
| **Password reset link** | The email address of a confirmed account | The link and when it expires |
| **Password changed** | The current email address | The date, and whether the password was changed or reset |

## Meetings: groups

| Kind | For | Data |
| :-- | :-- | :-- |
| **Proposal accepted** | The proposer | The group that was created |
| **Proposal rejected** | The proposer | The proposal and the reason |
| **Group archived** | Every member of the group | The group |

The hand-over of a group sends one notification to the party concerned at each step:

| Kind | For | Data |
| :-- | :-- | :-- |
| **Transfer requested** | The candidate | The group, the owner, what confirming needs (an active subscription, a payout account when the group has a meeting with a fee to come) and the link to the confirmation page |
| **Transfer declined** | The owner | The group and the candidate |
| **Transfer cancelled by the owner** | The candidate | The group |
| **Transfer cancelled by itself** | The owner | The group, and that the candidate left, was demoted or was locked |
| **Transfer completed** | The former owner and the new owner | The group, and the new role of each |

See [handing the group over](../meetings/group-life.md#handing-the-group-over) and [group creation](../meetings/group-creation.md).

## Meetings: meetings and comments

| Kind | For | Data |
| :-- | :-- | :-- |
| **Signed up** | The attendee of a meeting without a fee | The meeting |
| **Taken from the waitlist** | The member who was waiting | The meeting |
| **Awaiting payment** | The member who signs up to a meeting with a fee | The meeting, the places kept, and until when they are kept |
| **Place confirmed** | The attendee whose payment arrived | The meeting |
| **Place released** | The member who did not pay in time | The meeting |
| **Removed from a meeting** | The attendee | The meeting and the reason |
| **Meeting cancelled** | Every attendee, every member on the waitlist and every host, except the one who cancels | The meeting. Also sent for the meetings of a group that is archived |
| **Designated host** | The member designated by someone else, at the creation of the meeting or later | The meeting, who designated them, and the place they give up if they were signed up |
| **Removed as host** | The host removed by someone else | The meeting and who removed them |
| **Comment removed** | The author of a comment removed by someone else | The comment, who removed it and the reason |

Nothing is sent when somebody replies or likes. See [organizing meetings](../meetings/organization.md#notifications) and [commenting](../meetings/commenting.md#notifications).

## Payments

| Kind | For | Data |
| :-- | :-- | :-- |
| **Subscription bought** | The payer | The period and the expiration date |
| **Subscription renewed** | The payer | The period and the new expiration date. Sent when the payer renews and when an automatic renewal happens |
| **Renewal about to happen** | The payer whose automatic renewal is on | The date and the price |
| **Subscription about to expire** | The payer whose automatic renewal is off | The expiration date |
| **Subscription expired** | The payer | The expiration date |
| **Renewal price changing** | Each payer whose automatic renewal depends on the item that changed, a trial not yet paid included | The new price, whether it goes up or down, and that it applies at the next payment, which is the first one for a trial |
| **Automatic renewal turned off** | The payer, when the payment system gives up repeating a payment | That the renewal is off |
| **Trial started** | The payer | The date and the price of the first payment, as it stands that day |
| **Trial about to end** | The payer, 2 days before the end by default | The date and the price active at that moment |
| **Renewal period changed** | The payer | The date of the change and the price |

The [subscriptions page](../payments/subscriptions.md#notifications) and the [price list page](../payments/price-list.md#notifications) both describe the email about a price that is going to change: it is one kind, listed once.

Payments sends no notification for a [meeting fee](../payments/meeting-fees.md#notifications): what the attendee learns about their place comes from Meetings, above.
