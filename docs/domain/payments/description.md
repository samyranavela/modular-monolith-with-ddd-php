# Payments

Payments handles the money of the application. It sells **subscriptions** to the owners of meeting groups, collects the **fees** that attendees pay to take a place in a meeting, and keeps the **price list** of the subscriptions.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md).

## Who takes part

- A **payer** is the person seen from Payments. They exist here once their [user account](../accounts/description.md) is confirmed with the Member role, and they are the same person as the [user](../accounts/description.md#accounts) and the [member](../meetings/description.md#who-takes-part). Payments knows nothing of the person but their identity: neither their name nor their email address.
- An **administrator** is not a payer: they pay nothing and owe nothing. They manage the price list of the subscriptions.
- The **owner** of a meeting group is a payer who holds a subscription, or who is on the [free plan](subscriptions/description.md#without-a-subscription-the-free-plan) with their one group, and who may choose a payout account for each group, to receive the fees of its meetings.

## Two kinds of payment

Payments knows two kinds of payment, which have little in common except the external system that carries them.

| | Subscription | Meeting fee |
| :-- | :-- | :-- |
| Who pays | The owner of a group | An attendee, guests included |
| What for | Keeping the owner's groups able to organize meetings | Taking part in a meeting |
| Who receives the money | The application | The group, on the payout account its owner chose for it |
| Amount | The [price list](price-list/description.md), kept by an administrator | The fee of the meeting, set by the organizers in [Meetings](../meetings/organization/description.md#the-fee) |
| Time | A period, which ends and can be renewed | One payment per place, which waits a short time |
| What it changes | Whether the owner's groups are covered | Whether the attendee's place is guaranteed |

The page [subscriptions](subscriptions/description.md) describes the first, and the page [meeting fees](meeting-fees/description.md) the second.

## The payment system

The money moves through an **external payment system**. Payments asks it to collect an amount, and it answers that the amount was paid. Payments never sees or stores card data, and the choice of the system is a technical matter.

For an [automatic renewal](subscriptions/description.md#renewing-automatically), the system also keeps the payment method and repeats the payment, and Payments only learns that a period was paid.

Every payment waits for the system's answer for a limited time, and expires when it does not come. A refusal or an abandon changes nothing but the passing of that time. The payer of a subscription may start again once the payment has expired, and so does an attendee: they sign up again.

## The payout account

A payer receives money on a **payout account**, which they set up in Payments, and can hold several. The owner of a group chooses which of their validated payout accounts receives the fees of the group, which is a **group payout account**, decided in [Meetings](../meetings/group-life/description.md#the-payout-account-of-the-group). The application never holds the money of a fee: it goes from the attendee to the payout account of the group.

A payout account is never deleted: the payer **deactivates** it and can reactivate it, since the payments made on it are kept for legal reasons. While it is deactivated, no payment is created for it.

The application **never refunds**. A refund is the owner's decision, made in their own payment system according to their own policy. A payment that was made stays as it is when the attendee later loses the place, when the meeting is cancelled, or when it arrives after the place was released.

A meeting can only have a fee when its group has an **active group payout account**, which it is while its payout account is **validated**, so every fee is paid online. [Meetings](../meetings/organization/description.md#the-fee) needs to know when the payout account is validated, deactivated or reactivated, and that it is so from the moment it happens.

## What the context does

- **Selling subscriptions**: a payer starts with a free trial or buys a subscription for a month or a year, renews it before or after it ends, asks it to renew by itself or changes its period, and Payments is where it is known until when the subscription is valid, when it is about to end and when it has ended. See [subscriptions](subscriptions/description.md).
- **Collecting the fee of a meeting**: when an attendee signs up to a meeting that has a fee, Payments creates the payment, has the payment system collect it, and knows when it is paid or when it ran out of time. See [meeting fees](meeting-fees/description.md).
- **Keeping the price list**: an administrator sets the price of each subscription, for each period. See [the price list](price-list/description.md).
- **Setting up a payout account**: a payer sets up where they receive money, deactivates it and reactivates it. The owner of a group chooses it for the group in Meetings.
- **Reading**: a payer reads their own subscription and their own payments, among them those of the meetings they signed up to. An administrator reads the price list. Nobody reads the payments of someone else.

The payer is notified of the events of their subscription, its end included, and of the price changes that concern them, as listed in [subscriptions](subscriptions/description.md#notifications) and in [the price list](price-list/description.md#notifications).

## What Payments needs from other contexts

This page and the following ones describe the information a context needs from another, and how exact or fresh it must be. They never say how the information is obtained: whether it is requested at the moment it is needed or kept up to date beforehand is a technical choice, made in the implementation.

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| That a user account was confirmed with the Member role | [Accounts](../accounts/registration/description.md#confirming) | To have a payer | Each confirmation gives one payer, never lost and never twice. A payer must exist without noticeable delay, since they may buy at once |
| The attendee, the meeting, the amount owed and the group payout account where it goes, when a place is awaiting payment | [Meetings](../meetings/organization/description.md#the-fee) | To create the payment | Exact, once for each place. Without it there is no payment |
| That a place awaiting payment is lost, or that a meeting is cancelled | [Meetings](../meetings/organization/description.md#the-fee) | To cancel the payment that still waits | Prompt. When it is late, the payment may be paid after its end: it is recorded and not refunded |
| That a payout account is chosen for a group, or that a group payout account is removed or replaced | [Meetings](../meetings/group-life/description.md#the-payout-account-of-the-group) | To keep the group payout account, name it in a payment and refuse a payment for a deactivated one | Never lost, and in the order it happened |
| That a payer's user account is deleted | [Accounts](../accounts/account-deletion/description.md#what-happens-in-payments) | To end the subscription, deactivate the payout accounts and anonymise the payer | Never lost |
| The access of a user account: its role and its state | [Accounts](../accounts/description.md#access) | To allow or refuse each action. A locked payer is refused every action that changes something | Exact at every action, read at that moment and never kept |

## Who may do what

Payments decides, for each of its actions, **which role may do it**, such as changing a price or deactivating a payout account, and whether it changes something. It reads the role and the state of the person from [Accounts](../accounts/description.md#access) at each action. A locked payer is refused every action that changes something. What was set up before goes on by itself, such as an automatic renewal.

## Delays and limits

- The time a payment waits for the system's answer is configurable, 30 minutes by default. How long before its end a subscription is about to end is configurable for each period, 30 days for a year and 7 days for a month by default. The length of a free trial is configurable, 7 days by default, and so is the reminder before its first payment, 2 days by default. The limits of the free plan, one group and two meetings a month, are configurable too.
