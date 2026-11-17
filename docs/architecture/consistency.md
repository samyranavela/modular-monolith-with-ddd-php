# Consistency

[Between modules](between-modules.md) says what goes from one module to another. This page says how the application stays **right** while it does: how a change and its fact go together, how a fact has its effect once, and how a rule stays absolute when two operations meet. It answers D2 of [what the domain demands](drivers.md), and gives the guarantees that D1 and D6 ask of facts.

Concepts used: [reliable messaging](concepts/reliable-messaging.md) and, for one way to hold a rule across two modules, [saga](concepts/saga.md).

## One command, one module, one transaction

A command is handled in **one transaction of one module**. The transaction keeps what the aggregates changed and the facts that tell of it, and nothing of another module: **modules never share a transaction**, since each one's data is its own (see [D1](drivers.md#d1-each-context-has-its-own-data-and-needs-information-from-the-others)).

What must be decided at once is decided inside one module. What crosses modules goes by facts, which come later, and the few rules that must stay absolute across two modules are held in one of the ways of [between two modules](#between-two-modules).

## A change and its fact

A change that is kept without its fact, or a fact that is made known without its change, is a lie. The two are therefore kept **in the same transaction**: the fact is written in the module's own storage with the change, and delivered afterwards, to the modules that need it, **at least once**. If the transaction fails, there is neither change nor fact. The same holds for the facts handed to Notifications.

A fact is made known only when the decision is **final**. A change that may still be undone, as in some of the ways [between two modules](#between-two-modules), does not make its fact known before its verdict, so that no other module acts on something that is withdrawn.

Which technique carries the fact, a table and a relay or another, is decided in the technical decisions. See [reliable messaging](concepts/reliable-messaging.md#transactional-outbox).

## A fact received once

A fact arrives **at least once**, late, twice, or after another fact about the same thing, see [what the guarantees ask of a module](between-modules.md#what-the-guarantees-ask-of-a-module). Each receiving module obtains an effect **once**:

| Fact | How the effect is once |
| :-- | :-- |
| A signal: something happened, to someone | The identity of the fact is kept with the effect, **in the same transaction**. A fact whose identity is already kept changes nothing |
| A state: the whole state and its version | The view is replaced only if the version is higher, see [stale and out-of-order facts](between-modules.md#stale-and-out-of-order-facts) |
| A fact to notify | One notification for each fact and member: the pair identifies it, and a second one with the same pair changes nothing |

A fact that fails is **handled again**, within limits. A fact that still fails after them is **set aside and made visible**, with the reason: it is never dropped and never skipped. The rest goes on, so that one fact does not stop the others. See [reliable messaging](concepts/reliable-messaging.md#idempotent-receiver).

## Rules that hold at the same moment

A rule that spans several aggregates or several modules is **absolute** (D2): when two operations meet, one of them is refused. Three things settle it.

### The rule and its scope

A rule is written in the domain, as a **specification** or in a **domain service**. It reads what it needs through a port: the module's own data, or a question when the data belongs to another module. This says where the rule lives and what it reads. It does not stop two commands from passing it at the same time: each reads, finds the rule respected, and both save.

What stops it is the **scope** of the rule: what it protects, such as one meeting, one payer, one email address, one group in one month. A rule declares its scope, and the use case does the check and the change in **one transaction**, so that two commands of the same scope are served **one after the other**: the second sees the effect of the first and is refused. The technique that serialises them, a uniqueness constraint, a version, a lock, is decided in the technical decisions. A rule inside one aggregate needs only its version.

### Inside one module

Most absolute rules are of this kind. The scope names the data the rule reads and the change touches, and everything stays in one module and one transaction. See [the rules of D2](#the-rules-of-d2) for each scope.

A uniqueness rule is the simplest scope: the thing that must be unique is the scope.

### Between two modules

When the two sides of a rule are in two modules, no transaction serves them one after the other, since the modules stay independent (see [why not a shared transaction](#why-not-a-shared-transaction)). A question does not suffice either: its answer is true when it is given, and the other module may change right after. Take the sign-up to a meeting with a fee and the request to delete the owner's account. The sign-up is refused when the owner is locked, and the request is refused when a payment waits. If each asks the other first and then acts, both can find the other absent, and both succeed: a locked owner, and a payment that waits.

Whatever way holds the rule, it must offer three things:

- **A refusal on one side at least**, when the two operations meet: they do not both succeed.
- **Answers that are true when they are given.** A question reads only what is kept, never what is in progress.
- **Modules that stay independent**: no shared transaction, no constraint over the data of two modules.

How it is done is an implementation choice, taken in the technical decisions and not here. There are several ways, each with its price.

#### Ways to hold the rule

| Way | How it holds the rule | Fits when | Costs |
| :-- | :-- | :-- | :-- |
| **One owner for both sides** | The data of the two sides, or the rule, moves into one module, which decides alone and in one transaction | The two sides are in fact one concept, and the cut between modules was wrong | A module that grows, and the other must ask it or be told |
| **A saga by marks** | Each side marks what it is doing, then asks the other, then confirms or undoes | The rule must be absolute and the modules must stay apart | An intermediate state, an undo, a double refusal |
| **A saga by facts** | A request fact, then a confirmation fact | The other side is slow or outside, such as a payment system | Heavier and slower, the action waits |
| **A fact and a later check** | Each side acts on its own, and a fact corrects the outcome, or a final check cancels it | The domain accepts an order, or a late correction | Not absolute at the moment of the action, and the domain must say so |
| **A rule that does not cross** | The rule is stated again in the domain so that one module can hold it alone | The crossing came from how the rule was written | A change of the domain, to be agreed |

A shared transaction is a way too, and is not open here: the independence of the modules rules it out, see below. The saga by marks is written out as an example in [the saga concept](concepts/saga.md#example-mark-then-ask), to show what a way asks of the model of a module, such as an intermediate state. It is not the decision.

#### The three cases

Each case is a rule whose two sides are in two modules, and what must hold whatever the way.

| Rule | Side in a first module | Side in a second module | What must hold |
| :-- | :-- | :-- | :-- |
| A sign-up to a meeting with a fee, and the deletion request of the owner | **Meetings:** the sign-up is refused when the owner is locked | **Accounts:** the request is refused when a payment waits for the owner or for their places | Not both: no payment waits for a locked owner |
| A payout account removed, and a fee or an acceptance of a hand-over | **Payments:** the removal is refused when a meeting with a fee is yet to start | **Meetings:** a fee or an acceptance is refused when the owner holds no validated payout account | Not both: no meeting with a fee without a validated account |
| The final check of a deletion, and a host who leaves | **Accounts:** the deletion is carried out only if no open item stands | **Meetings:** a member may leave unless they are the last host | Not both: no meeting is left without a host by a deletion |

The third case ends a rule that the domain states in two places: the check and the carrying out of a deletion are [one decision](../domain/accounts/account-deletion.md#carrying-out-the-deletion), and a host who was not the last may become the last when the other host leaves. Outside the moment of the final check, a departure goes through, and the final check cancels the deletion if the member is now the last host, as the domain says.

#### Where a fact is enough

Not every rule across two modules needs more than a fact:

- **The domain accepts an order.** A hand-over whose candidate becomes locked is cancelled by itself, and the first request wins: the fact of the lock does this when it arrives, even just after the designation, see [handing the group over](../domain/meetings/group-life.md#handing-the-group-over).
- **The receiver can hold the rule alone.** A deleted member must receive no notification, even when a deletion and a creation happen together. Notifications keeps the identity of the deleted member when the fact arrives, and refuses any creation for them after: the race is inside Notifications, and its scope is the member.

#### Why not a shared transaction

Two modules in one process, over one database, could share a transaction. It would make these rules simple and without a double refusal. It is not retained, since the modules stay independent:

- It ties the modules to a single database and a single process, and a module could no longer be separated. The ability to take a module out is what the cut into modules is for, see [ready to be extracted](concepts/modular-monolith.md#ready-to-be-extracted): replacing the adapter that calls it must be enough, and a transaction or a constraint that spans two modules would have to be rebuilt first.
- It asks a module to take locks on the data of another, or to call a method of another that changes something. [Between modules](between-modules.md#two-kinds-of-message) allows no such message.
- It asks for a table or a constraint that two modules share, such as a table of claimed email addresses with a uniqueness constraint that both write. [No constraint crosses two modules](modules-and-layers.md#one-module-for-each-context): the rule is held in each module for its own data, and by one of the ways above across the two.
- Locks that cross modules bring an order of locks to keep, and the risk of a deadlock.

The literature does not agree on this. Some authors accept the shared transaction as the simplest consistency model, at the price of a transaction that widens as more is attached to it, and others forbid any transaction or constraint between modules that share a database. This project takes the second view, because it wants the modules ready to be extracted. See [Spring Modulith](https://docs.spring.io/spring-modulith/reference/events.html) for the first view and [integration styles](https://www.kamilgrzybek.com/blog/posts/modular-monolith-integration-styles) for the second.

## The rules of D2

The rules of [D2](drivers.md#d2-rules-that-hold-even-when-two-things-happen-at-the-same-moment), each with its scope, or the other side. Where a row of D2 has two sides, it is split in two.

| Rule | Where | Scope, or the other side |
| :-- | :-- | :-- |
| The last host of a meeting cannot leave or be removed | One module, Meetings | The meetings concerned. The case with a deletion in progress is the third of [the three cases](#the-three-cases) |
| A free-plan group has at most two meetings a month | One module, Meetings | The group and the month |
| A free-plan member owns one group at the acceptance | One module, Meetings | The proposer. That a member ever held a subscription is a question to Payments, and only becomes more permissive, since a subscription is never deleted |
| No sign-up to a meeting with a fee during a hand-over | One module, Meetings | The group |
| No sign-up to a meeting with a fee while the owner is locked | Meetings and Accounts | First of the three cases |
| One payment waiting and one subscription for a payer | One module, Payments | The payer |
| One active price for each period | One module, Payments | The period |
| A payout account is not removed while a meeting with a fee is to come. One validated and one pending account | Payments and Meetings. One module for the accounts | Second of the three cases. For the accounts, the payer |
| An email address belongs to one account, pending or confirmed | One module, Accounts | The email address, over every account |
| At least one administrator remains | One module, Accounts | The administrators together |
| A deletion is checked and carried out as one decision | Accounts, with Meetings and Payments | Third of the three cases |
| No notification exists for a deleted member. One notification for a fact and a member | One module, Notifications | The member. The pair of the fact and the member |

## What stays for later

- **The way to hold the three cases across two modules**: the technical decisions, among the ways above.
- **What starts a command on a date**, such as the expiry of a mark left by a failure, a payment waiting or a pending account: the page on time.
- **Which technique** serialises two commands of the same scope, relays the facts and keeps the identity of the facts handled: the technical decisions.
- **The outside systems** and the late answer of a payment system: the page on outside systems.
