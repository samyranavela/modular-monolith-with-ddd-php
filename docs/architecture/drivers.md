# What the domain demands

An architecture answers demands. These are the ones the domain makes, each taken from the domain pages. They are written as demands, not as solutions: how the code meets them is the subject of the pages that follow. Each has a number, so that later pages can say which demand they answer.

## D1. Each context has its own data, and needs information from the others

The four contexts each hold their own data: a person is an account, a member and a payer, under three names, in three places. When a context needs something from another, its description says how **exact** and how **fresh** it must be, in a table "What X needs from other contexts". The same few expectations come back:

| Expectation | Meaning | Example |
| :-- | :-- | :-- |
| **Exact at the moment of an action** | The answer is right when the action is decided, not a moment before | A hand-over to a locked candidate is refused, see [group life](../domain/meetings/group-life.md#handing-the-group-over) |
| **Exact at an action, and again later** | The same check twice, at the request and when it is carried out | The open items of a deletion, see [deleting an account](../domain/accounts/account-deletion.md#carrying-out-the-deletion) |
| **Without noticeable delay** | A short wait is fine, a long one is not | A renewed subscription must let the owner organize at once, see [Meetings](../domain/meetings/description.md#what-meetings-needs-from-other-contexts) |
| **Never lost, never twice** | Every fact reaches the context that needs it, exactly once in its effect | A confirmed account gives one member and one payer, see [confirming](../domain/accounts/registration.md#confirming) |
| **As of a given day** | A snapshot is enough | The export of a person's data, see [exporting the data](../domain/accounts/account-deletion.md#exporting-the-data) |

The domain never says how information travels. The architecture must offer a way for each of these expectations.

## D2. Rules that hold even when two things happen at the same moment

Every rule that spans several aggregates, or several contexts, is **absolute**: when two operations meet, one succeeds and the other is refused. No rule is left to chance. The domain states them where they live:

| Rule | Stated in |
| :-- | :-- |
| A meeting always has at least one host: the last host who counts (a locked host does not) cannot leave or be removed | [organizing meetings](../domain/meetings/organization.md#hosts) |
| A free-plan group has at most two meetings a month | [creating a meeting](../domain/meetings/organization.md#creating-a-meeting) |
| A free-plan member owns one group, checked at the acceptance of a proposal | [creating a meeting group](../domain/meetings/group-creation.md#deciding) |
| No sign-up to a meeting with a fee during a hand-over, or from the moment the owner is locked | [the fee](../domain/meetings/organization.md#the-fee) |
| A payer has one payment waiting and one subscription at most | [subscriptions](../domain/payments/subscriptions.md#buying) |
| One active price for each period | [the price list](../domain/payments/price-list.md#one-active-price-per-period) |
| A group payout account is removed only when no meeting of the group has a fee yet to start | [the payout account of the group](../domain/meetings/group-life.md#the-payout-account-of-the-group) |
| An email address belongs to one account, pending or confirmed | [registering](../domain/accounts/registration.md#registering) |
| At least one administrator remains, and a deletion is checked and carried out as one decision | [deleting an account](../domain/accounts/account-deletion.md) |
| No notification exists for a deleted member | [Notifications](../domain/notifications/description.md#what-notifications-needs-from-other-contexts) |

Some of these rules cross aggregates, or even contexts, and still have to be decided in one go. The architecture must give each one a place that can say no, see [consistency](consistency.md#rules-that-hold-at-the-same-moment).

## D3. Every action is guarded by the access of the person

Each context decides which role may do each of its own actions, and says whether it **reads** or **changes**. Accounts holds the roles, the **lock** of an account whose deletion is pending and the subscription as part of the **access**, and the context **reads the access from Accounts at every action**, never keeping it. A locked account is refused every action that changes something, with four exceptions. See [access](../domain/accounts/description.md#access).

The architecture must put one check in front of every use case, decided with data that another context owns.

## D4. Things happen because time passes

Several rules start with no request at all:

| What | When | Stated in |
| :-- | :-- | :-- |
| A pending account, from a registration or an invitation, expires | 48 hours | [registration](../domain/accounts/registration.md#expiring) |
| A link to confirm a new email address expires, and a password reset link | 48 hours, and 1 hour | [profile management](../domain/accounts/profile-management.md#delays-and-limits) |
| A payment waiting for the payment system expires | 30 minutes | [meeting fees](../domain/payments/meeting-fees.md#paying) |
| A free trial ends, and its reminder | 7 days, and 2 days before its end | [subscriptions](../domain/payments/subscriptions.md#trying) |
| A subscription is about to end or to renew, then ends | 30 days for a year, 7 days for a month, before the end | [subscriptions](../domain/payments/subscriptions.md#when-a-subscription-ends) |
| A deletion is carried out | After a grace period, 14 days | [deleting an account](../domain/accounts/account-deletion.md#locked-account) |
| An export of data expires | 7 days | [exporting the data](../domain/accounts/account-deletion.md#exporting-the-data) |
| Anonymised payments are deleted | After 10 years | [deleting an account](../domain/accounts/account-deletion.md#carrying-out-the-deletion) |

Every delay is configurable. The date is the rule: from the moment an expiry date has passed, the answer is no, even when nothing has yet cleaned up. The architecture must make things happen on a date, and must never depend on the cleaning to be right.

## D5. Outside systems are slow, unreliable and sometimes late

A **payment system** collects the amounts, validates the payout accounts and carries the automatic renewals. An **email** channel delivers the notifications. Both answer in their own time, and may answer after the application has moved on: a fee paid after the place was released is recorded and never refunded. The domain never sees card data.

The architecture must keep these systems behind a boundary that the domain does not know, and must accept an answer that arrives late, twice, or not at all.

## D6. One fact, one notification

A context hands Notifications a fact with its kind, its member and its data, and each member concerned receives **one notification, never lost and never twice**, even when the same fact is handed twice. Required messages follow the same rule, to an email address. Delivery is retried within limits. See [Notifications](../domain/notifications/description.md#one-fact-one-notification).

## D7. Reading has its own demands

Reading is not the mirror of writing:

- What a person sees depends on who they are: any member sees the groups and the meetings of a group, only group members see members and attendees, hosts, owner and organizers see the waitlist, an administrator reads everything in Meetings and a locked member still reads. See [what each role sees](../domain/meetings/group-creation.md#what-each-role-sees) and the reading sections of each page.
- Lists are filtered and sorted, and counted: proposals by status, notifications by unread.
- The views are the **read models** of the event stormings, each with its own need of freshness.

The architecture must let a reading take the shape of its screen, without going through the rules of writing.

## D8. A person can be forgotten

The deletion of an account is carried out in every context, **never lost**: the account is deleted, the member leaves every group, the payer is anonymised, the inbox is deleted. Some things stay on purpose: comments and decisions become those of a "deleted member", and payments are kept anonymised. Each context can also **export** what it holds about a person. See [deleting an account](../domain/accounts/account-deletion.md).

The architecture must let every context erase, anonymise and export its own data, and be told of a deletion whatever happens.

## What these demands ask of the architecture

| Demand | Asks for |
| :-- | :-- |
| D1 | A way to give each context what it needs from another, for every expectation of exactness and freshness |
| D2 | A place for each absolute rule, even across aggregates or contexts, that cannot be bypassed |
| D3 | A single check before every use case, outside the rules of the domain |
| D4 | Work triggered by a date, with the date as the rule |
| D5 | A boundary around outside systems that tolerates late, repeated or missing answers |
| D6 | Facts that are never lost and never doubled, from a context to Notifications |
| D7 | A reading path separate from the writing path |
| D8 | A deletion that reaches every context, and an export from each |
