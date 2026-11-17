# Payments: meeting fees

A meeting can ask its attendees for a **fee**. This page follows the payment of that fee, from the moment a member signs up to the moment the payment is paid, expires or is cancelled.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](meeting-fees-conceptual-model.md) and the behaviour in the [event storming](meeting-fees-event-storming.md).

## What a meeting fee payment is

A **meeting fee payment** belongs to **one attendee for one meeting**. It holds the **amount**, in euros, which [Meetings](../meetings/organization.md#the-fee) computes as (1 + the attendee's guests) times the fee of the meeting, and the **group payout account** where it goes, which Meetings names. Payments does not decide the amount: it receives it.

A payment waits for the [payment system](description.md#the-payment-system) for a short time, then ends in one of three ways:

- **Paid**: the payment system confirmed it, and the place is guaranteed.
- **Expired**: the time went by, and the place is released.
- **Cancelled**: the attendee lost the place, or the meeting was cancelled, before paying.

A payment never leaves an end state. A member who signs up again later gets a **new payment**.

## Signing up to a meeting with a fee

A meeting that has a fee takes a **place in two steps**, and has **no waitlist**: a place that frees up goes to the next member who signs up. When a member signs up, Meetings keeps the places for them, **awaiting payment**, and Payments needs to know what they owe. Payments creates the payment and asks the payment system to collect it for the [payout account](description.md#the-payout-account) of the group.

The places are kept **only while the payment waits**: 30 minutes by default, configurable. Until the payment system confirms, **the place is not guaranteed**. A meeting without a fee has no payment step: the member is an attendee at once.

A member has **one payment waiting at most for a meeting**. The group must have an **active group payout account**, which it is while its payout account is validated: Meetings checks it when the fee of a meeting is set and at every sign-up, and Payments refuses to create the payment otherwise. A sign-up to a meeting with a fee is therefore impossible while the payout account is deactivated.

## Paying

The attendee pays on the page of the payment system, and the money goes from the attendee to the **payout account of the group**: the application never holds it.

- **Paid.** When the payment system confirms, the payment is paid. Meetings needs to know it, and confirms the attendee's place.
- **Expired.** When the answer does not come in time, the payment expires. Meetings needs to know it, and releases the places. The member signs up again if they still want to come.

The amount is fixed when the payment is created. A later change of the fee of the meeting touches neither this payment nor those already paid.

## Losing the place, and a cancelled meeting

When an attendee who has not paid yet loses the place, because they say they will not come, are removed, become a host or leave the group, Payments needs to know it, and the payment is **cancelled**. When the meeting is cancelled, every payment of the meeting that still waits is cancelled. In both cases the payment system is asked to close what it opened.

## Refunds

The application **never refunds**. A payment that is paid stays as it is when its attendee later loses the place or when the meeting is cancelled. A payment that reaches Payments after its end, because it was already in progress with the payment system, is recorded and changes no place. The owner decides on a refund, in their own payment system and according to their own policy.

## A hand-over of the group

A group enters a [hand-over](../meetings/group-life.md#handing-the-group-over) only when **no payment of its meetings waits**, which Meetings checks when the owner makes the request. From that moment until the hand-over ends, Meetings closes the sign-ups to the meetings of the group that have a fee, so that no payment can be created. When a meeting of the group that has a fee has yet to start, the candidate's confirmation, which completes the hand-over, needs a **validated payout account** that they choose for the group: it becomes the group payout account and ends the former owner's. The payments already made stay with the former owner's group payout account, which is deactivated, never deleted. A payment never changes receiver.

Meetings closes the same sign-ups while the owner of the group is [locked](../accounts/account-deletion.md#locked-account) by a pending deletion of their account, so that no payment is created for an owner who may be gone.

## What others need to know

The facts about a fee that Meetings needs, and how exact they must be, are listed in [what Meetings needs from other contexts](../meetings/description.md#what-meetings-needs-from-other-contexts); what Payments needs from Meetings is listed in [what Payments needs from other contexts](description.md#what-payments-needs-from-other-contexts).

| Fact | Concerns |
| :-- | :-- |
| A payment is paid | The attendee and the meeting |
| A payment expires | The attendee and the meeting |
| A payout account is validated | The payer who holds it |
| A payout account is deactivated | The payer who holds it |
| A payout account is reactivated | The payer who holds it |

Payments decides nothing about a place: it only knows whether the payment is paid.

## The payout account

A payer sets up where they receive money, and can hold **several** payout accounts, each of which can serve one group or several. The payment system must **validate** an account before it counts. The payer can **deactivate** it at any time and **reactivate** it later: it is never deleted, since the payments made on it are kept for legal reasons. While it is deactivated, no payment is created for it and a payment that waits for it is cancelled. Meetings needs to know when an account is validated, deactivated or reactivated: it deactivates, then reactivates, the group payout accounts that name it, which is what stops the groups that name it from taking a sign-up to a meeting with a fee, and lets them again.

The holder of the payout account decides alone: the sign-ups to the meetings with a fee of the groups concerned become impossible until they reactivate it, and Payments does not ask whether such a meeting exists.

## The group payout account

The owner of a group chooses which of their validated payout accounts receives the fees of the group. [Meetings](../meetings/group-life.md#the-payout-account-of-the-group) decides it: who may choose it, replace it or remove it, and what a meeting with a fee needs. Payments keeps the pair of the payout account and the group, and its status, **active or deactivated**, from what Meetings makes known: active when it is chosen, deactivated when Meetings removes or replaces it.

- A payment is created only for a pair that is **active** and whose payout account is **validated**. The rule is absolute for the payout account, since both live in Payments: when a deactivation and the creation of a payment happen at the same moment, only one succeeds. For the pair, Meetings refuses the sign-up first.
- When Meetings removes a group payout account, for example at a transfer, the payments made for it stay with the pair, as a record. The pair is activated again if the same payout account is chosen again for the same group.

## Notifications

Payments sends **no notification** for a fee: the receipt is the payment system's. What the attendee learns of their place, awaiting payment, confirmed or released, is notified by [Meetings](../meetings/organization.md#notifications).

## Reading

- **Own payments**: for each one, the meeting, the amount and its status. The attendee reads only their own.
- **The place of an attendee**: hosts, the owner and the organizers see whether an attendee is awaiting payment or confirmed. This is read in Meetings, not here. For the details of a payment, the owner goes to their own payment system.

An administrator reads no payment.

## Who may do what

Payments allows two kinds of action on this page to the Member role: reading one's own meeting fee payments and managing a payout account, which covers setting it up, deactivating it and reactivating it. A locked payer only reads, see [who may do what](description.md#who-may-do-what).

## Delays

The time a payment waits for the payment system is configurable, 30 minutes by default, the shortest a session of the payment system can last.
