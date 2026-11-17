# Payments: the price list

The **price list** holds the price of a subscription for each period. An administrator keeps it, and every purchase or renewal of a [subscription](subscriptions.md) reads from it. This page describes its items, how they change, and what price a payer pays.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](price-list-conceptual-model.md) and the behaviour in the [event storming](price-list-event-storming.md).

## What an item is

A **price list item** is the price of one subscription for a **period**, a month or a year. The price is the same for every payer, whatever their country, and the same price applies to a first purchase, to a renewal, manual or automatic, and to the first payment after a trial: there is no price for new payers and another for the others.

The period **identifies** what the item is the price of and never changes. The price is the only thing that can. **Every price is in euros**: the application has one currency and never converts.

An item is **active** or **inactive**. Only active items count: an inactive one is kept, and can be activated again, but nobody is sold anything at its price.

## One active price per period

For a given period, **one item is active at most**, so a payer is always charged the one active price. Creating an item, or activating one, is refused when another is already active for the same period: the administrator deactivates it first, or changes its price instead.

The rule involves every item, so it does not belong to one of them. A domain service checks it, reading what is stored; the rule is absolute: when two administrators act at the same moment, only one succeeds and the other is refused.

## Keeping the list

An administrator does four things:

- **Create an item.** It is created active, with its period and price.
- **Change the price.** Only the price changes.
- **Deactivate an item.** The price is no longer offered. Deactivating an inactive item changes nothing.
- **Activate an item.** The price is offered again. Activating an active item changes nothing.

An item is never deleted, and its period is never edited: a price for the other period is another item.

## When there is no price

When the list holds **no active price** for the period a payer chooses, the purchase or the renewal is refused, and the payer is told that no price is offered for this period. Payments never invents a price and never falls back on the other period.

## The price a payer pays

The price is **the amount of the active item**, as is. There is no promotion, no discount code and no special rate: the price list is the only source of a price. A longer period is cheaper only because its item holds its own price.

The price a payer accepts when buying or renewing must equal the active price at that moment, otherwise the operation is refused and they see the new price. The payment keeps the price it was created with. An [automatic renewal](subscriptions.md#renewing-automatically) keeps the price of its next payment, and takes the new one when the price of the active item of its period changes. A [trial](subscriptions.md#trying) follows the same item until its first payment.

## Notifications

Each of these is a notification handed to [Notifications](../notifications/description.md): the member finds it in their inbox and receives it by email, as [the catalogue](../notifications/catalogue.md#payments) lists.

When the **price of an active item** changes, a notification goes to every payer whose [automatic renewal](subscriptions.md#renewing-automatically) depends on that item, that is, with the same period, a [trial](subscriptions.md#trying) whose first payment is still to come included.

It tells them the new price, whether it goes up or down, and that it applies at their next payment, which is the first one for a trial. They can turn automatic renewal off before.

Nothing else is sent: nothing for the creation, the activation or the deactivation of an item, and nothing to a payer without automatic renewal, who sees the active price when they buy or renew. Deactivating an item does not touch an automatic renewal already on: it keeps the last price.

## Reading

- **An administrator** reads the whole list, active and inactive items alike, with their period and price.
- **A payer** reads the **price they would pay**, for a period, when they are about to buy or renew. They never read the list itself, nor an inactive item.

## Permissions

Payments defines two permissions for the actions of this page: **managing the price list**, which covers creating, changing the price of, activating and deactivating an item, and **reading the whole price list**. [Accounts](../accounts/description.md#permissions) grants them to the Administrator role. A payer reads the price they would pay under the permission to [take out a subscription](subscriptions.md#permissions), which Accounts grants to the Member role.
