# Payments

Payments handles the money of the application. It sells **subscriptions** to the owners of meeting groups, collects the **fees** that attendees pay to take a place in a meeting, and keeps the **price list** of the subscriptions.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md).

## Who takes part

- A **payer** is the person seen from Payments. They exist here once their [registration](../registrations/description.md) is confirmed with the Member role, and they are the same person as the [user](../useraccess/description.md) and the [member](../meetings/description.md#who-takes-part). Payments keeps nothing of the person but their identity: no name and no email address are copied.
- An **administrator** is not a payer: they pay nothing and owe nothing. They manage the price list of the subscriptions.
- The **owner** of a meeting group is a payer who holds a subscription, and who may receive the fees of the meetings of their groups.

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

Every payment waits for the system's answer for a limited time, and expires when it does not come. A refusal or an abandon changes nothing but the passing of that time. The payer of a subscription may try again at once, and an attendee whose payment expired signs up again.

## The payout account

The owner of a group receives the fees of its meetings on a **payout account**, which they set up in Payments, once, for all their groups. The application never holds the money of a fee: it goes from the attendee to the owner's account.

The application **never refunds**. A refund is the owner's decision, made in their own payment system according to their own policy. A payment that was made stays as it is when the attendee later loses the place, when the meeting is cancelled, or when it arrives after the place was released.

A meeting can only have a fee when the owner of its group has a **validated** payout account, so every fee is paid online. [Meetings](../meetings/organization.md#creating-a-meeting) needs to know whether the owner has one, which Payments tells it when the payment system validates the account.

## What the context does

- **Selling subscriptions**: a payer buys a subscription for a month or a year, renews it before or after it ends, or asks it to renew by itself, and Payments tells the rest of the application until when it is valid, when it is about to end and when it has ended. See [subscriptions](subscriptions.md).
- **Collecting the fee of a meeting**: when an attendee signs up to a meeting that has a fee, Payments creates the payment, takes it, and tells Meetings when it is paid or when it ran out of time. See [meeting fees](meeting-fees.md).
- **Keeping the price list**: an administrator sets the price of each subscription, by country, period and kind. See [the price list](price-list.md).
- **Setting up a payout account**: the owner of a group sets up where the fees of their meetings are paid.
- **Reading**: a payer reads their own subscription and their own payments, among them those of the meetings they signed up to. An administrator reads the price list. Nobody reads the payments of someone else.

Two emails go out, to the payer: a confirmation when a subscription is bought, and one when it is renewed. The reminders and notices about the end of a subscription belong to [the life of a meeting group](../meetings/group-life.md#when-the-subscription-ends).

## Permissions

Every action of Payments is protected by a permission that Payments defines, such as changing a price or setting up a payout account. [UserAccess](../useraccess/description.md#permissions) holds the roles and which permissions each one grants, and Payments never names a role.

## Left open

- **Delays.** The time a payment waits for the system's answer is configurable, 30 minutes by default. How long before the end of a subscription Payments says it is about to end is configurable, 30 days by default.
