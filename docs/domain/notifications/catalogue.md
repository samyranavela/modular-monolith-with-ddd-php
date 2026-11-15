# The catalogue of Notifications

The catalogue lists every **kind** of notification the application sends, grouped by the context that has something to report. A kind says whom it is for, what data it needs, and which channels carry it, see [the catalogue](description.md#the-catalogue). Terms are defined in the [Notifications glossary](../../../src/Notifications/GLOSSARY.md).

The names are descriptive: the identifier of each kind is a matter for the implementation. The text of the templates is not written here, only the data it needs.

Unless a table says otherwise, every kind goes to the **inbox and by email**. A [required message](description.md#required-messages) is by email only, to an address. An administrator is never a recipient.

When something is cancelled or removed for a **reason**, the reason is part of the data and shown to the member.

## Registrations

Required messages, to an address.

| Kind | For | Data |
| :-- | :-- | :-- |
| **Address confirmation** | The address being registered, or the address of an invited administrator | The confirmation link, when it expires, and the role it leads to. Sent at the registration, at the invitation, and again with a new link when someone registers once more with an address that waits for its confirmation |
| **Account already exists** | The address of someone who registers with an address already used by a user | Nothing but the fact |

See [registering again with the same email](../registrations/description.md#registering-again-with-the-same-email).

## UserAccess

| Kind | For | Data |
| :-- | :-- | :-- |
| **Deletion requested** | The member | The date the deletion will be carried out, what will happen, the groups to be archived |
| **Deletion cancelled** | The member | Nothing but the fact |
| **Deletion ended by open items** | The member | The open items that stood in the way |
| **Export ready** | The member | Until when the export can be downloaded |
| **Deletion carried out** | *Required message.* The address erased by the deletion | The date of the deletion |

See [account deletion](../useraccess/account-deletion.md#notifications). An administrator who is deleted is not told.

## Meetings: groups

| Kind | For | Data |
| :-- | :-- | :-- |
| **Proposal accepted** | The proposer | The group that was created |
| **Proposal rejected** | The proposer | The proposal and the reason |
| **Group archived** | Every member of the group | The group |
| **Subscription about to expire** | The owner | The date, and the groups concerned |
| **Subscription expired** | The owner | The groups that are no longer covered |

The hand-over of a group sends one notification to the party concerned at each step:

| Kind | For | Data |
| :-- | :-- | :-- |
| **Transfer requested** | The candidate | The group, the owner, and what accepting means (a subscription, a payout account) |
| **Transfer accepted** | The owner | The group and the candidate. The transfer completes once the candidate's subscription is valid |
| **Transfer declined** | The owner | The group and the candidate |
| **Transfer cancelled by the owner** | The candidate | The group |
| **Transfer cancelled by itself** | The owner | The group, and that the candidate left or was demoted |
| **Subscription of the candidate confirmed** | The owner | The group and the candidate |
| **Transfer completed** | The former owner and the new owner | The group, and the new role of each |

See [handing the group over](../meetings/group-life.md#handing-the-group-over), [when the subscription ends](../meetings/group-life.md#when-the-subscription-ends) and [group creation](../meetings/group-creation.md).

## Meetings: meetings and comments

| Kind | For | Data |
| :-- | :-- | :-- |
| **Signed up** | The attendee of a meeting without a fee | The meeting |
| **Taken from the waitlist** | The member who was waiting | The meeting |
| **Awaiting payment** | The member who signs up to a meeting with a fee | The meeting, the places kept, and until when they are kept |
| **Place confirmed** | The attendee whose payment arrived | The meeting |
| **Place released** | The member who did not pay in time | The meeting |
| **Removed from a meeting** | The attendee | The meeting and the reason |
| **Meeting cancelled** | Every attendee, and every member on the waitlist | The meeting. Also sent for the meetings of a group that is archived |
| **Comment removed** | The author of a comment removed by someone else | The comment, who removed it and the reason |

Nothing is sent when somebody replies or likes. See [organizing meetings](../meetings/organization.md#notifications) and [commenting](../meetings/commenting.md#notifications).

## Payments

| Kind | For | Data |
| :-- | :-- | :-- |
| **Subscription bought** | The payer | The period and the expiration date |
| **Subscription renewed** | The payer | The period and the new expiration date. Sent when the payer renews and when an automatic renewal happens |
| **Renewal about to happen** | The payer whose automatic renewal is on | The date and the price |
| **Renewal price changing** | Each payer whose automatic renewal depends on the item that changed | The new price, whether it goes up or down, and that it applies at the next renewal |
| **Automatic renewal turned off** | The payer, when the payment system gives up repeating a payment | That the renewal is off |
| **Trial started** | The payer | The date and the price of the first payment |
| **Trial about to end** | The payer, 2 days before the end by default | The date and the price |
| **Renewal period changed** | The payer | The date of the change and the price |

The [subscriptions page](../payments/subscriptions.md#notifications) and the [price list page](../payments/price-list.md#notifications) both describe the email about a price that is going to change: it is one kind, listed once.

Payments sends no notification for a [meeting fee](../payments/meeting-fees.md#notifications): what the attendee learns about their place comes from Meetings, above.
