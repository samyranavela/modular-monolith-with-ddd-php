# Payments

Handles the money of the application: the subscriptions of the owners of meeting groups, the fees that attendees owe for a meeting, and the price list of the subscriptions.

## Language

**Payer**:
A member seen from Payments. Created when an account with the Member role is confirmed, and known only by their identity. Administrators are not payers.
_Avoid_: Customer, buyer

**Payment System**:
The external system that collects an amount and answers that it was paid. Payments never sees card data.
_Avoid_: Gateway, bank

**Payout Account**:
Where the owner of a group receives the fees of its meetings, set up once for all their groups and validated by the payment system before it counts. A meeting can have a fee only if the owner of its group has a validated one.
_Avoid_: Bank account, wallet

**Money Value**:
An amount in euros that is not negative. The application has one currency.
_Avoid_: Price (for the amount itself)

**Subscription**:
What a payer holds to keep their groups able to organize meetings. It is valid until an expiration date, then expires, and a payer has one at most.
_Avoid_: Plan, membership

**Subscription Period**:
The length a subscription is bought or renewed for: a month or a year. The set is fixed.
_Avoid_: Duration, term

**Subscription Payment**:
The purchase of a first subscription: a price for a period, waiting for the payment system. It ends paid or expired.
_Avoid_: Order

**Subscription Renewal Payment**:
The purchase of a further period of an existing subscription, expired or not. It ends paid or expired.
_Avoid_: Extension

**Automatic Renewal**:
An option of a subscription, off by default except during a free trial, where it is on from the start, that makes the payment system repeat the payment at the end of each period. It keeps a period, and the price of its next payment, which follows the active price of the price list; the payer can turn it off at any time.
_Avoid_: Auto-pay, recurring payment

**Price List Item**:
The price of one subscription for a period, the same for every payer, whatever their country, for a first purchase and for a renewal. It is active or inactive, and only one is active for a given period: every payment is at that active price.
_Avoid_: Tariff, rate

**Free Trial**:
The first 7 days of a subscription, which a payer who never held one starts without paying. Automatic renewal is on from the start, so the first payment is taken at the end, at the active price at that moment, unless the payer turns it off. It happens once per payer.
_Avoid_: Demo, test period

**Free Plan**:
What a payer has while they hold no subscription and never did: one group and two meetings a month. It is left for good with the first subscription, a trial included.
_Avoid_: Starter, basic plan

**Meeting Fee Payment**:
What an attendee pays the owner of the group to take a place in a meeting that has a fee, guests included. It is created when the attendee signs up, waits a short time for the payment system, and ends paid, expired or cancelled. Until it is paid, the place is not guaranteed.
_Avoid_: Debt, ticket, transaction

**Anonymised Payer**:
A payer whose account was deleted. Their subscription ended with no refund, their payout account was removed, and their payments are kept as the accounting record, without an identity.
_Avoid_: Deleted payer
