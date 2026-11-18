# Payments

Handles the money of the application: the subscriptions of the owners of meeting groups, the fees that attendees owe for a meeting, and the price list of the subscriptions.

## Language

**Payer**:
A member seen from Payments. Created when a user account with the Member role is confirmed, and known only by their identity, the same in every role the payer takes: the one who holds a subscription, the one who receives fees on a payout account, the one who pays for a place. Administrators are not payers.
_Avoid_: Customer, buyer

**Payment System**:
The external system that collects an amount and answers that it was paid. Payments never sees card data.
_Avoid_: Gateway, bank

**Payout Account**:
Where a payer receives money from the payment system, validated by that system before it counts, so that it can be chosen to receive the fees of groups. A payer can hold several, and one serves one group or many. It is pending, validated or deactivated, and is never deleted: the payer deactivates it, and reactivates it when they want it back. While it is deactivated, no payment is created for it.
_Avoid_: Bank account, wallet, account (alone: it may mean the user account)

**Group Payout Account**:
The payout account that the owner of a group chose to receive the fees of its meetings, seen from Payments as the pair of the payout account and the group, active or deactivated. [Meetings](../Meetings/GLOSSARY.md) decides it: Payments only keeps it, so that a payment names where its fee goes. When Meetings removes it, for example at a transfer, Payments keeps the pair, deactivated, with the payments made for it, which are kept for legal reasons. It is activated again if the same payout account is chosen again for the same group. A payment is refused for a deactivated pair, and for a payout account that is not validated.
_Avoid_: Payout link, assignment

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
What an attendee pays to the group payout account of a meeting that has a fee, to take a place in it, guests included. It is created when the attendee signs up, waits a short time for the payment system, and ends paid, expired or cancelled. Until it is paid, the place is not guaranteed.
_Avoid_: Debt, ticket, transaction

**Anonymised Payer**:
A payer whose user account was deleted. Their subscription ended with no refund, their payout accounts were deactivated, and their payments are kept as the accounting record, without an identity.
_Avoid_: Deleted payer
