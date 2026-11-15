# Payments

Payments handles the money of the application. It sells **subscriptions** to the owners of meeting groups, collects the **fees** that attendees pay to take a place in a meeting, and keeps the **price list** of the subscriptions.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md).

## Who takes part

- A **payer** is the person seen from Payments. They exist here once their [registration](../registrations/description.md) is confirmed with the Member role, and they are the same person as the [user](../useraccess/description.md) and the [member](../meetings/description.md#who-takes-part). Payments knows nothing of the person but their identity: neither their name nor their email address.
- An **administrator** is not a payer: they pay nothing and owe nothing. They manage the price list of the subscriptions.
- The **owner** of a meeting group is a payer who holds a subscription, or who is on the [free plan](subscriptions.md#without-a-subscription-the-free-plan) with their one group, and who may receive the fees of the meetings of their groups.

## Two kinds of payment

Payments knows two kinds of payment, which have little in common except the external system that carries them.

| | Subscription | Meeting fee |
| :-- | :-- | :-- |
| Who pays | The owner of a group | An attendee, guests included |
| What for | Keeping the owner's groups able to organize meetings | Taking part in a meeting |
| Who receives the money | The application | The owner of the group, on their payout account |
| Amount | The [price list](price-list.md), kept by an administrator | The fee of the meeting, set by the organizers in [Meetings](../meetings/organization.md#the-fee) |
| Time | A period, which ends and can be renewed | One payment per place, which waits a short time |
| What it changes | Whether the owner's groups are covered | Whether the attendee's place is guaranteed |

The page [subscriptions](subscriptions.md) describes the first, and the page [meeting fees](meeting-fees.md) the second.

## The payment system

The money moves through an **external payment system**. Payments asks it to collect an amount, and it answers that the amount was paid. Payments never sees or stores card data, and the choice of the system is a technical matter.

For an [automatic renewal](subscriptions.md#renewing-automatically), the system also keeps the payment method and repeats the payment, and Payments only learns that a period was paid.

Every payment waits for the system's answer for a limited time, and expires when it does not come. A refusal or an abandon changes nothing but the passing of that time. The payer of a subscription may start again once the payment has expired, and so does an attendee: they sign up again.

## The payout account

The owner of a group receives the fees of its meetings on a **payout account**, which they set up in Payments, once, for all their groups. The application never holds the money of a fee: it goes from the attendee to the owner's account.

The application **never refunds**. A refund is the owner's decision, made in their own payment system according to their own policy. A payment that was made stays as it is when the attendee later loses the place, when the meeting is cancelled, or when it arrives after the place was released.

A meeting can only have a fee when the owner of its group has a **validated** payout account, so every fee is paid online. [Meetings](../meetings/organization.md#creating-a-meeting) needs to know whether the owner has one, and that it is so from the moment the payment system validates the account.

## What the context does

- **Selling subscriptions**: a payer starts with a free trial or buys a subscription for a month or a year, renews it before or after it ends, asks it to renew by itself or changes its period, and Payments is where it is known until when the subscription is valid, when it is about to end and when it has ended. See [subscriptions](subscriptions.md).
- **Collecting the fee of a meeting**: when an attendee signs up to a meeting that has a fee, Payments creates the payment, takes it, and knows when it is paid or when it ran out of time. See [meeting fees](meeting-fees.md).
- **Keeping the price list**: an administrator sets the price of each subscription, by country, period and kind. See [the price list](price-list.md).
- **Setting up a payout account**: the owner of a group sets up where the fees of their meetings are paid.
- **Reading**: a payer reads their own subscription and their own payments, among them those of the meetings they signed up to. An administrator reads the price list. Nobody reads the payments of someone else.

The payer is notified of the events of their subscription and of the price changes that concern them, as listed in [subscriptions](subscriptions.md#notifications) and in [the price list](price-list.md#notifications). The reminders and notices about the end of a subscription belong to [the life of a meeting group](../meetings/group-life.md#when-the-subscription-ends).

## What Payments needs from other contexts

This page and the following ones describe the information a context needs from another, and how exact or fresh it must be. They never say how the information is obtained: whether it is requested at the moment it is needed or kept up to date beforehand is a technical choice, made in the implementation.

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| That a registration was confirmed with the Member role | [Registrations](../registrations/description.md) | To have a payer | Each confirmation gives one payer, never lost and never twice. A payer must exist without noticeable delay, since they may buy at once |
| The attendee, the meeting, the amount owed and the owner who receives it, when a place is awaiting payment | [Meetings](../meetings/organization.md#the-fee) | To create the payment | Exact, once for each place. Without it there is no payment |
| That a place awaiting payment is lost, or that a meeting is cancelled | [Meetings](../meetings/organization.md#the-fee) | To cancel the payment that still waits | Prompt. When it is late, the payment may be paid after its end: it is recorded and not refunded |
| Whether an owner has a meeting with a fee that has yet to start | [Meetings](../meetings/organization.md#the-fee) | To refuse the removal of their payout account | Exact at the moment of the removal |
| Whether a user holds a role that grants a permission | [UserAccess](../useraccess/description.md#permissions) | To allow or refuse each action | Exact at every action |

## Permissions

Every action of Payments is protected by a permission that Payments defines, such as changing a price or setting up a payout account. [UserAccess](../useraccess/description.md#permissions) holds the roles and which permissions each one grants, and Payments never names a role.

## Delays and limits

- The time a payment waits for the system's answer is configurable, 30 minutes by default. How long before the end of a subscription Payments says it is about to end is configurable, 30 days by default. The length of a free trial is configurable, 7 days by default, and so is the reminder before its first payment, 2 days by default. The limits of the free plan, one group and two meetings a month, are configurable too.
