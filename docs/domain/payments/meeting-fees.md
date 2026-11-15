# Payments: meeting fees

A meeting can ask its attendees for a **fee**. This page follows the payment of that fee, from the moment a member signs up to the moment the payment is paid, expires or is cancelled.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](meeting-fees-conceptual-model.md) and the behaviour in the [event storming](meeting-fees-event-storming.md).

## What a meeting fee payment is

A **meeting fee payment** belongs to **one attendee for one meeting**. It holds the **amount**, in euros, which [Meetings](../meetings/organization.md#the-fee) computes as (1 + the attendee's guests) times the fee of the meeting, and the **owner of the group who receives it**, whom Meetings names. Payments does not decide the amount: it receives it.

A payment waits for the [payment system](description.md#the-payment-system) for a short time, then ends in one of three ways:

- **Paid**: the payment system confirmed it, and the place is guaranteed.
- **Expired**: the time went by, and the place is released.
- **Cancelled**: the attendee lost the place, or the meeting was cancelled, before paying.

A payment never leaves an end state. A member who signs up again later gets a **new payment**.

## Signing up to a meeting with a fee

A meeting that has a fee takes a **place in two steps**, and has **no waitlist**: a place that frees up goes to the next member who signs up. When a member signs up, Meetings keeps the places for them, **awaiting payment**, and Payments needs to know what they owe. Payments creates the payment and asks the payment system to collect it for the owner's [payout account](description.md#the-payout-account).

The places are kept **only while the payment waits**: 30 minutes by default, configurable. Until the payment system confirms, **the place is not guaranteed**. A meeting without a fee has no payment step: the member is an attendee at once.

A member has **one payment waiting at most for a meeting**. The owner of the group must have a validated payout account, which Meetings checks when the fee of a meeting is set.

## Paying

The attendee pays on the page of the payment system, and the money goes from the attendee to the **payout account of the owner**: the application never holds it.

- **Paid.** When the payment system confirms, the payment is paid. Meetings needs to know it, and confirms the attendee's place.
- **Expired.** When the answer does not come in time, the payment expires. Meetings needs to know it, and releases the places. The member signs up again if they still want to come.

The amount is fixed when the payment is created. A later change of the fee of the meeting touches neither this payment nor those already paid.

## Losing the place, and a cancelled meeting

When an attendee who has not paid yet loses the place, because they say they will not come, are removed, become a host or leave the group, Payments needs to know it, and the payment is **cancelled**. When the meeting is cancelled, every payment of the meeting that still waits is cancelled. In both cases the payment system is asked to close what it opened.

## Refunds

The application **never refunds**. A payment that is paid stays as it is when its attendee later loses the place or when the meeting is cancelled. A payment that reaches Payments after its end, because it was already in progress with the payment system, is recorded and changes no place. The owner decides on a refund, in their own payment system and according to their own policy.

## A hand-over of the group

A group enters a [hand-over](../meetings/group-life.md#handing-the-group-over) only when **no payment of its meetings waits**, which Meetings checks when the owner makes the request. From that moment until the hand-over is fully validated or ends, Meetings closes the sign-ups to the meetings of the group that have a fee, so that no payment can be created. When a meeting of the group that has a fee has yet to start, the candidate's acceptance counts only if their payout account is **validated**. A payment never changes receiver.

## What others need to know

The facts about a fee that Meetings needs, and how exact they must be, are listed in [what Meetings needs from other contexts](../meetings/description.md#what-meetings-needs-from-other-contexts); what Payments needs from Meetings is listed in [what Payments needs from other contexts](description.md#what-payments-needs-from-other-contexts).

| Fact | Concerns |
| :-- | :-- |
| A payment is paid | The attendee and the meeting |
| A payment expires | The attendee and the meeting |
| A payout account is validated | The owner |
| A payout account is removed | The owner |

Payments decides nothing about a place: it only knows whether the payment is paid.

## The payout account

The owner of a group sets up where the fees of their meetings are paid, once, for all their groups. The payment system must **validate** the account before it counts, and replacing it starts a new one, which takes over once validated. They can **remove** it only when no meeting of their groups that has a fee has yet to start: as long as the date and time of such a meeting have not passed, it stays. Payments needs to know, at the moment of the removal, whether such a meeting exists, and the rule is absolute: when the removal of the account and the setting of a fee on a meeting happen at the same moment, only one succeeds and the other is refused. Likewise a payer has **at most one validated account and one pending**, whatever happens at the same moment. The refusal does **not** apply when the removal is part of an [account erasure](../useraccess/account-deletion.md): the meetings of the groups concerned are cancelled or the groups handed over, so no fee waits for the account. Meetings needs to know when an owner's account is validated, which is what lets that owner's groups give a meeting a fee.

## Notifications

Payments sends **no notification** for a fee: the receipt is the payment system's. What the attendee learns of their place, awaiting payment, confirmed or released, is notified by [Meetings](../meetings/organization.md#notifications).

## Reading

- **Own payments**: for each one, the meeting, the amount and its status. The attendee reads only their own.
- **The place of an attendee**: hosts, the owner and the organizers see whether an attendee is awaiting payment or confirmed. This is read in Meetings, not here. For the details of a payment, the owner goes to their own payment system.

An administrator reads no payment.

## Permissions

Payments defines two permissions for the actions of this page: reading one's own meeting fee payments and managing a payout account, which covers setting it up, replacing it and removing it. [UserAccess](../useraccess/description.md#permissions) grants them to the Member role.

## Delays

The time a payment waits for the payment system is configurable, 30 minutes by default, the shortest a session of the payment system can last.
