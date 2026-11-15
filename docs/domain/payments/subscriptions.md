# Payments: subscriptions

A **subscription** is what an owner holds for their meeting groups to be able to organize meetings. This page follows it from its purchase to its end.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](subscriptions-conceptual-model.md) and the behaviour in the [event storming](subscriptions-event-storming.md).

## What a subscription is

A payer holds **one subscription at most**, which can start with a [free trial](#trying). It has a **period**, which is a month or a year and which is the length of the last purchase, and an **expiration date**. It is **active** until that date and **expired** after.

A subscription covers **every group its holder owns**, with no limit on their number: [Meetings](../meetings/group-life.md#covering-the-group) decides what coverage allows, and Payments only says until when the subscription is valid.

A subscription renews by itself only when its holder turned on [automatic renewal](#renewing-automatically). Otherwise it ends on its date, and the payer renews it when they want to. A subscription is not cancelled, and there is no refund.

## Without a subscription: the free plan

A payer who **never held a subscription**, a trial included, is on the **free plan**. It has no date and no price: it is what a payer has until their first subscription, and it lets them own **one group** and organize **two meetings a month**, both limits configurable. [Meetings](../meetings/group-life.md#covering-the-group) applies them.

The free plan is **left for good**: as soon as a payer holds a subscription, even an expired one, they never return to it. Payments adds nothing to announce for it: it announces the creation of a subscription, and a member of whom Meetings knows no expiration date has never held one.

## Buying

A payer **without a subscription** buys one. They choose a **period** and a **country**, and see the price that the [price list](price-list.md) holds for that country, that period and the kind *new*. The price they accept must be the active price: when it changed in the meantime, the purchase is refused and the payer sees the new one.

The purchase creates a **subscription payment**, which holds the price and the period and waits for the [payment system](description.md#the-payment-system). A payer who already has a subscription, expired or not, cannot buy another: they renew.

- **Paid.** When the payment system confirms the payment, the subscription is created, active, and it expires one period after that moment. An email confirms the purchase.
- **Expired.** When the answer does not come in time, the payment ends and nothing is created. The payer may buy again.

A payer has **one payment waiting at a time**, purchase or renewal. They pay it or let it expire before starting another.

The price is fixed when the payment is created. A change of the price list does not touch a payment that waits.

## Trying

A payer who never held a subscription can start with a **free trial** instead of paying at once.

- **Once only.** A trial is for a payer who has never held a subscription, a trial included. A payer who tried once, and let it end, buys like anyone else.
- **How long.** **7 days**, configurable.
- **How it starts.** The payer chooses a period and a country, sees the price of the kind *new*, and agrees to be charged at the end of the trial. The [payment system](description.md#the-payment-system) collects and keeps their payment method, and charges nothing yet.
- **During the trial.** The subscription exists at once, active, and expires 7 days later. It covers the payer's groups like any other. [Automatic renewal](#renewing-automatically) is on from the start, for the chosen period and at the price accepted.
- **At the end.** The payment system takes the first payment, and the subscription is renewed exactly as by an automatic renewal: the period adds up to the end of the trial, so nothing is lost. The payer is told of the date and the price before it happens.
- **Not continuing.** The payer turns automatic renewal off before the end. The subscription runs until the end of the trial, nothing is charged, and the payer cannot try again.
- **A failed payment.** As for any automatic renewal: Payments does nothing in the meantime, and the subscription expires if no payment arrives before.

## Renewing

A payer **with a subscription**, active or expired, renews it. The mechanism is the same as for a purchase, with the kind *renewal* of the price list: they choose a period and a country, and a **subscription renewal payment** waits for the payment system.

- **Paid.** The subscription is active and expires one period later. When it was still active, the period **adds up** to the current expiration date, so nothing is lost by renewing early. When it had expired, the period starts **at the moment of the payment**. An email confirms the renewal.
- **Expired.** The payment ends and the subscription is unchanged.

Renewing may change the period: a subscription bought for a month can be renewed for a year.

A payer whose automatic renewal is on cannot renew by hand: they turn it off first.

## Renewing automatically

A payer can ask their subscription to **renew by itself**. The [payment system](description.md#the-payment-system) keeps the payment method and repeats the payment, once per period, with its own rules for retrying a payment that failed. Payments does not charge anyone: it learns that a period was paid.

- **Turning it on.** The payer chooses a period and agrees to be charged again at the end of each one. They can do it while buying or renewing, or later on a subscription that is still active, in which case the first automatic payment is at the expiration date. An expired subscription is renewed first, then the option is turned on.
- **Turning it off.** The payer can turn it off whenever they want. The subscription then runs until its date, without refund, and ends there.
- **A period paid.** Each time the payment system announces a period paid, the subscription is renewed exactly as by a manual renewal: when it is still active the period adds up to the expiration date, otherwise it starts at the moment of the payment. The same email confirms it.
- **A failed payment.** The payment system retries by its own rules, and Payments does nothing in the meantime: the subscription runs until its date, and expires if no payment arrives before. When the payment system gives up, the option is turned off.
- **The price.** An automatic renewal keeps the period, the country and the price fixed when the option was turned on, and they change only at a renewal or when the option is turned on again. When the price list changes the price that applies to a payer whose option is on, the new price takes effect at their next automatic renewal, and an **email announces the change** to that payer as soon as the list changes, whether the price goes up or down. The payer who does not accept it turns the option off themselves.

## Changing the period

A payer whose subscription is **active** can change the period of its automatic renewal, from a month to a year or the other way. The change **never takes effect before the end of the current period**.

- **From a month to a year.** The month in progress is not touched. At its end, the payer is charged the price of the year and the subscription then runs for 12 months from that date.
- **From a year to a month.** The year in progress runs to its end, without refund, then the subscription renews by the month.
- **No credit, no refund.** Nothing overlaps and nothing is deducted: the new period starts exactly where the old one ends.
- **Without automatic renewal.** The payer turns it on with the new period, which asks them to agree to be charged. A payer who prefers to pay by hand [renews](#renewing) with the period they want, and the period adds up as usual.

The payer is told by email of the date of the change and of the price.

## When a subscription ends

Nobody asks for it: time does. Payments watches the expiration dates.

- **About to end.** A time before the expiration date, which is configurable and **30 days by default**, Payments announces that the subscription is about to expire, once for each expiration date, **only when automatic renewal is off**. When the option is turned off after that moment, the announcement is made at once. A renewal brings a new date, hence a new announcement later.
- **About to renew.** At the same moment, 30 days before by default, when automatic renewal is **on**, nothing is announced to the rest of the application, since the subscription is going to renew. Payments itself emails the payer that the subscription is about to renew, with the date and the price.
- **Expired.** When the date passes, the subscription expires, and Payments announces it.

An expired subscription stays, and can be renewed. The renewal price applies, not the price of a purchase.

## What Payments announces

The rest of the application does not ask Payments anything: it listens.

| When | What is announced |
| :-- | :-- |
| A subscription is created or renewed | Its new expiration date, for its holder |
| A subscription is about to expire | Its holder and its expiration date |
| A subscription expires | Its holder |

[Meetings](../meetings/group-life.md#when-the-subscription-ends) keeps the expiration date of each member's subscription and decides from it whether a group is covered. It also sends the reminder and the notice to the owner.

### A hand-over of a group

The hand-over of a group to an organizer has **no special case in Payments**. The organizer who accepts must have an active subscription, and takes one out or renews like any payer, even if they were on the [free plan](#without-a-subscription-the-free-plan); the announced date lets Meetings [complete the transfer](../meetings/group-life.md#handing-the-group-over).

The former owner's subscription stays theirs. It runs until its end, without refund and without any transfer: it simply stops covering the group.

## Emails

| When | Who is told |
| :-- | :-- |
| A subscription is bought | The payer |
| A subscription is renewed, by the payer or automatically | The payer |
| A subscription with automatic renewal on is about to renew | The payer, with the date and the price |
| The price of an automatic renewal is going to change | The payer, with the new price |
| A trial starts | The payer, with the date and the price of the first payment |
| A trial is about to end | The payer, with the date and the price, **2 days before** its end by default, configurable |
| The period of an automatic renewal is changed | The payer, with the date of the change and the price |

The reminder of a subscription that is about to **expire**, with automatic renewal off, is sent by [Meetings](../meetings/group-life.md#when-the-subscription-ends) to the owner, for the groups concerned.

## Reading

- **Own subscription**: its period, its status, its expiration date, and whether automatic renewal is on, with its period. A payer without a subscription sees that they have none.
- **Own subscription payments**: for each one, its period, its price, its date and its status.

A payer reads nothing of someone else's. An administrator reads no subscription for now.

## Permissions

Payments defines two permissions for the actions of this page: taking out a subscription, which covers buying it, renewing it and turning automatic renewal on and off, and reading one's own subscription and payments. [UserAccess](../useraccess/description.md#permissions) grants them to the Member role.

## Left open

- **When an automatic payment fails.** Telling the payer that the payment failed, or that the payment system gave up, is left to the payment system's own notices for now, and to the Notifications context later.
- **How many retries, and over how long.** It is the payment system's rule, not the domain's.
