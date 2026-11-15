# Payments: the price list

The **price list** holds the price of a subscription for each country, period and kind. An administrator keeps it, and every purchase or renewal of a [subscription](subscriptions.md) reads from it. This page describes its items, how they change, and what price a payer pays.

Terms are defined in the [Payments glossary](../../../src/Payments/GLOSSARY.md). The structure is in the [conceptual model](price-list-conceptual-model.md) and the behaviour in the [event storming](price-list-event-storming.md).

## What an item is

A **price list item** is the price of one subscription for a **country**, a **period** (a month or a year) and a **kind**:

- *new*: the price of a first purchase;
- *renewal*: the price of a renewal, manual or automatic.

The country, the period and the kind **identify** the item and never change. The price is the only thing that can. **Every price is in euros**: the application has one currency and never converts. A country is only a way to set a different price for a different market.

An item is **active** or **inactive**. Only active items count: an inactive one is kept, and can be activated again, but nobody is sold anything at its price.

## One active price per country, period and kind

For a given country, period and kind, **one item is active at most**. Creating an item, or activating one, is refused when another is already active for the same triple: the administrator deactivates it first, or changes its price instead.

The rule involves every item, so it does not belong to one of them. A domain service checks it, reading what is stored; the rule is absolute: when two administrators act at the same moment, only one succeeds and the other is refused.

## Keeping the list

An administrator does four things:

- **Create an item.** It is created active, with its country, period, kind and price.
- **Change the price.** Only the price changes.
- **Deactivate an item.** The price is no longer offered. Deactivating an inactive item changes nothing.
- **Activate an item.** The price is offered again. Activating an active item changes nothing.

An item is never deleted, and its country, period and kind are never edited: a price for another triple is another item.

## When there is no price

A payer buys or renews for a country. When the list holds **no active price** for that country, that period and that kind, the purchase or the renewal is refused, and the payer is told that no price is offered for this country. Payments never invents a price and never falls back on another country.

## The price a payer pays

The price is **the amount of the active item**, as is. There is no promotion, no discount code and no special rate: the price list is the only source of a price. A longer period is cheaper only because its item holds its own price.

The price a payer accepts when buying or renewing must equal the active price at that moment, otherwise the operation is refused and they see the new price. The payment keeps the price it was created with. An [automatic renewal](subscriptions.md#renewing-automatically) keeps the price of its next payment, and takes the new one when the price of the item it depends on changes: the *renewal* item, or, for a [trial](subscriptions.md#trying) until its first payment, the *new* item.

## Notifications

Each of these is a notification handed to [Notifications](../notifications/description.md): the member finds it in their inbox and receives it by email, as [the catalogue](../notifications/catalogue.md#payments) lists.

When the **price of an active item** changes, a notification goes to every payer whose [automatic renewal](subscriptions.md#renewing-automatically) depends on that item, that is, with the same country and the same period:

- for an item of the kind *renewal*, the payers whose renewal depends on it, which is every automatic renewal but a trial not yet paid;
- for an item of the kind *new*, **only** the payers whose [trial](subscriptions.md#trying) depends on it, that is, whose first payment is still to come.

It tells them the new price, whether it goes up or down, and that it applies at their next payment, which is the first one for a trial. They can turn automatic renewal off before.

Nothing else is sent: nothing for the creation, the activation or the deactivation of an item, and nothing for a change of a *new* price to anyone but those payers. Deactivating an item does not touch an automatic renewal already on: it keeps the last price.

## Reading

- **An administrator** reads the whole list, active and inactive items alike, with their country, period, kind and price.
- **A payer** reads the **price they would pay**, for a country, a period and a kind, when they are about to buy or renew. They never read the list itself, nor an inactive item.

## Permissions

Payments defines two permissions for the actions of this page: **managing the price list**, which covers creating, changing the price of, activating and deactivating an item, and **reading the whole price list**. [UserAccess](../useraccess/description.md#permissions) grants them to the Administrator role. A payer reads the price they would pay under the permission to [take out a subscription](subscriptions.md#permissions), which UserAccess grants to the Member role.
