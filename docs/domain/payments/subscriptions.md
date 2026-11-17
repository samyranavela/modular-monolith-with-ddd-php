# Payments: subscriptions

A **subscription** is what an owner holds for their meeting groups to be able to organize meetings. This page follows it from its purchase to its end.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](subscriptions-conceptual-model.md) and the behaviour in the [event storming](subscriptions-event-storming.md).

## What a subscription is

A payer holds **one subscription at most**, which can start with a [free trial](#trying). It has a **period**, which is a month or a year and which is the length of the last purchase, and an **expiration date**. It is **active** until that date and **expired** after.

A subscription covers **every group its holder owns**, with no limit on their number: [Meetings](../meetings/group-life.md#covering-the-group) decides what coverage allows, and Payments only knows until when the subscription is valid.

A subscription renews by itself only when its holder turned on [automatic renewal](#renewing-automatically). Otherwise it ends on its date, and the payer renews it when they want to. A subscription is not cancelled, and there is no refund.

## Without a subscription: the free plan

A payer who **never held a subscription**, a trial included, is on the **free plan**. It has no date and no price: it is what a payer has until their first subscription, and it lets them own **one group** and organize **two meetings a month**, both limits configurable. [Meetings](../meetings/group-life.md#covering-the-group) applies them.

The free plan is **left for good**: as soon as a payer holds a subscription, even an expired one, they never return to it. Payments adds nothing for it: a payer who holds no subscription, and never held one, is on the free plan, and that is all [Meetings](../meetings/description.md#what-meetings-needs-from-other-contexts) needs to know.

## Buying

A payer **without a subscription** buys one, or starts with a [free trial](#trying). They choose a **period**, and see the price that the [price list](price-list.md) holds for it. The price they accept must be the active price: when it changed in the meantime, the purchase is refused and the payer sees the new one.

The purchase creates a **subscription payment**, which holds the price and the period and waits for the [payment system](description.md#the-payment-system). A payer who already has a subscription, expired or not, cannot buy another: they renew.

- **Paid.** When the payment system confirms the payment, the subscription is created, active, and it expires one period after that moment. A notification confirms the purchase.
- **Expired.** When the answer does not come in time, the payment ends and nothing is created. The payer may buy again.

A payer has **one payment waiting at a time**, purchase or renewal. They pay it or let it expire before starting another. A payer also has **one subscription at most**, and the rule is absolute: when a payer starts two trials, or buys twice, or starts a trial and buys, at the same moment, only one subscription is created and the other operation is refused.

The price is fixed when the payment is created. A change of the price list does not touch a payment that waits.

## Trying

A payer who never held a subscription can start with a **free trial** instead of paying at once.

- **Once only.** A trial is for a payer who has never held a subscription, a trial included. A payer who tried once holds an expiring or expired subscription, and renews like anyone else, at the active price.
- **How long.** **7 days**, configurable.
- **How it starts.** The payer chooses a period, sees the active price, and agrees to be charged at the end of the trial **the price active at that moment**, which may not be the one they saw: they are notified if it changes, and can turn automatic renewal off before the end. The [payment system](description.md#the-payment-system) collects and keeps their payment method, and charges nothing yet.
- **During the trial.** The subscription exists at once, active, and expires 7 days later. It covers the payer's groups like any other. [Automatic renewal](#renewing-automatically) is on from the start, for the chosen period.
- **At the end.** The payment system takes the first payment, at the **active price** at that moment, for the period of the trial, and the subscription is renewed exactly as by an automatic renewal: the period adds up to the end of the trial, so nothing is lost. The payer is told of the date and the price before it happens.
- **Not continuing.** The payer turns automatic renewal off before the end. The subscription runs until the end of the trial, nothing is charged, and the payer cannot try again.
- **A failed payment.** As for any automatic renewal: Payments does nothing in the meantime, and the subscription expires if no payment arrives before.

## Renewing

A payer **with a subscription**, active or expired, renews it. The mechanism is the same as for a purchase, at the same active price of the price list: they choose a period, and a **subscription renewal payment** waits for the payment system.

- **Paid.** The subscription is active and expires one period later. When it was still active, the period **adds up** to the current expiration date, so nothing is lost by renewing early. When it had expired, the period starts **at the moment of the payment**. A notification confirms the renewal.
- **Expired.** The payment ends and the subscription is unchanged.

Renewing may change the period: a subscription bought for a month can be renewed for a year.

A payer whose automatic renewal is on cannot renew by hand: they turn it off first.

## Renewing automatically

A payer can ask their subscription to **renew by itself**. The [payment system](description.md#the-payment-system) keeps the payment method and repeats the payment, once per period, with its own rules for retrying a payment that failed. Payments does not charge anyone: it learns that a period was paid.

- **Turning it on.** The payer chooses a period and agrees to be charged again at the end of each one. They can do it while buying or renewing, or later on a subscription that is still active, in which case the first automatic payment is at the expiration date. An expired subscription is renewed first, then the option is turned on.
- **Turning it off.** The payer can turn it off whenever they want. The subscription then runs until its date, without refund, and ends there.
- **A period paid.** Each time the payment system confirms a period paid, the subscription is renewed exactly as by a manual renewal: when it is still active the period adds up to the expiration date, otherwise it starts at the moment of the payment. The same notification confirms it.
- **A failed payment.** The payment system retries by its own rules, how many times and over how long being its own decision, and Payments does nothing in the meantime: the subscription runs until its date, and expires if no payment arrives before. When the payment system gives up, the option is turned off and a **notification tells the payer** so, since nothing else would.
- **The price.** An automatic renewal keeps the period fixed when the option was turned on, and the **price of its next payment**, which follows the price list: the active price of that period, for a [trial](#trying) until its first payment as for any other. When the price of that active item changes, the next payment takes the **new price**, up or down, and a **notification tells the payer** as soon as the list changes, with the date from which it applies. Deactivating an item changes nothing: the renewal keeps the last price. The payer who does not accept the new price turns the option off themselves.

## Changing the period

A payer whose subscription is **active** can change the period of its automatic renewal, from a month to a year or the other way. The change **never takes effect before the end of the current period**.

- **From a month to a year.** The month in progress is not touched. At its end, the payer is charged the active price of the year, and the subscription then runs for 12 months from that date.
- **From a year to a month.** The year in progress runs to its end, without refund, then the subscription renews by the month.
- **No credit, no refund.** Nothing overlaps and nothing is deducted: the new period starts exactly where the old one ends.
- **Without automatic renewal.** The payer turns it on with the new period, which asks them to agree to be charged. A payer who prefers to pay by hand [renews](#renewing) with the period they want, and the period adds up as usual.

The payer is notified of the date of the change and of the price.

## When a subscription ends

Nobody asks for it: time does. Payments watches the expiration dates.

- **About to end.** A time before the expiration date, which depends on the period in progress and is configurable, **30 days for a year and 7 days for a month** by default, the subscription is **about to expire**, once for each expiration date, **only when automatic renewal is off**. When the option is turned off after that moment, it is about to expire at once. A renewal brings a new date, hence it is about to expire again later. Payments notifies the payer, with the date.
- **About to renew.** At the same moment, 30 days before the end of a year and 7 days before the end of a month by default, when automatic renewal is **on**, the subscription is not about to expire, since it is going to renew. Payments itself notifies the payer that the subscription is about to renew, with the date and the price. A [trial](#trying) is too short for this delay: its reminder is sent 2 days before its end, by default.
- **Expired.** When the date passes, the subscription **expires**, and Payments notifies the payer.

The payer is notified whether or not they own a group: the subscription is theirs.

An expired subscription stays, and can be renewed, at the active price like any payment.

One thing ends a subscription before its date: the [deletion of its holder's account](../accounts/account-deletion.md#what-happens-in-payments). The automatic renewal is stopped, no new period is bought, and there is no refund.

## What others need to know

The facts of a subscription that other contexts need, and how exact they must be, are listed in [what Meetings needs from other contexts](../meetings/description.md#what-meetings-needs-from-other-contexts). In short: its expiration date, with every renewal taken into account, and whether the payer ever held one.

| Fact | Who and what |
| :-- | :-- |
| A subscription is created or renewed | Its new expiration date, for its holder |

[Meetings](../meetings/group-life.md#when-the-subscription-ends) decides from the expiration date whether a group is covered. That a subscription is about to expire, or has expired, stays in Payments, which notifies the payer.

### A hand-over of a group

The hand-over of a group to an organizer has **no special case in Payments**. The organizer who accepts must have an active subscription, and takes one out or renews like any payer, even if they were on the [free plan](#without-a-subscription-the-free-plan); the expiration date lets Meetings [complete the transfer](../meetings/group-life.md#handing-the-group-over).

The former owner's subscription stays theirs. It runs until its end, without refund and without any transfer: it simply stops covering the group.

## Notifications

Each of these is a notification handed to [Notifications](../notifications/description.md): the member finds it in their inbox and receives it by email, as [the catalogue](../notifications/catalogue.md#payments) lists.

| When | Who is told |
| :-- | :-- |
| A subscription is bought | The payer |
| A subscription is renewed, by the payer or automatically | The payer |
| A subscription with automatic renewal on is about to renew | The payer, with the date and the price |
| A subscription with automatic renewal off is about to expire | The payer, with the expiration date |
| A subscription expires | The payer |
| The price an automatic renewal depends on is going to change, a trial that has not yet been paid included | The payer, with the new price, and that it applies at the next payment |
| The payment system gives up repeating a payment | The payer, who is told that automatic renewal is turned off |
| A trial starts | The payer, with the date and the price of the first payment, as it stands that day |
| A trial is about to end | The payer, with the date and the price active at that moment, **2 days before** its end by default, configurable |
| The period of an automatic renewal is changed | The payer, with the date of the change and the price |

## Reading

- **Own subscription**: its period, its status, its expiration date, whether it started as a trial, and whether automatic renewal is on, with its period. A payer without a subscription sees that they have none.
- **Own subscription payments**: for each one, its period, its price, its date and its status.

A payer reads nothing of someone else's. An administrator reads no subscription.

## Permissions

Payments defines two permissions for the actions of this page: taking out a subscription, which covers starting a trial, buying, renewing, turning automatic renewal on and off and changing its period, and reading one's own subscription and payments. [Accounts](../accounts/description.md#permissions) grants them to the Member role.
