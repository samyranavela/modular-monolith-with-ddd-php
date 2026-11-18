# Between modules

The four modules of [modules and layers](modules-and-layers.md) hold their own data and own rules. They still have to work together: a confirmed account creates a member, a paid fee confirms a place, a deletion reaches every context. This page says **what goes from one module to another, in which form, and with which guarantees**. It answers D1, D6 and D8 of [what the domain demands](drivers.md).

Concepts used: [event-driven architecture](concepts/event-driven-architecture.md) and [modular monolith](concepts/modular-monolith.md). The tools come from the [DDD Starter Modelling Process](https://ddd-crew.github.io/ddd-starter-modelling-process/): a [context map](https://github.com/ddd-crew/context-mapping) for the relations, [domain message flow modelling](https://github.com/ddd-crew/domain-message-flow-modelling) for the scenarios, and the inbound and outbound sections of the [bounded context canvas](https://github.com/ddd-crew/bounded-context-canvas) for the lists of messages.

## Two kinds of message

A module never reads the data of another, nor calls its inside. It sends or answers **messages**, each of one of two kinds:

| | Fact | Question |
| :-- | :-- | :-- |
| Says | Something happened in the module, in the past tense | Is this true, or what is this, right now? |
| Goes | From the module where it happened, to every module that needs to know | From the module that needs the answer, to the module that owns it |
| Moment | Later: the receiver reacts in its own time | At once: the sender waits for the answer |
| Changes the receiver | Yes, through a command of the receiver (see [use cases](use-cases.md#where-a-use-case-comes-from)) | Never |
| Owner of its words | The module where it happened | The module that answers |
| Fits | "Never lost, never twice" and "without noticeable delay" | "Exact at the moment of an action" |

A **fact** is the default, and the way the modules stay independent: the sender does not know who reacts, nor whether anyone does. A **question** exists for the one thing a fact cannot give: an answer that is right *when an action is decided*, not a moment before. It is allowed only for that, and only to read.

There is no third kind. A module cannot order another to change: it states a fact, and the other module decides what it does with it, with its own rules.

## The Contract has two parts

Everything a module says to the others is in its **Contract**, in the words of its own glossary. The Contract is split in two, so that one sees at a glance what a module makes known and what it lets others ask:

| Part | Holds | Used by |
| :-- | :-- | :-- |
| **Facts** | The facts the module makes known, each a **data type** with its data. The owner produces them and implements nothing | The modules that need to know: each has a handler in its Ui that turns the fact into a command |
| **Questions** | The questions the module answers, each a method of an **interface**, with the type of its answer. The owner implements the interface, in its Application | The modules that need an answer at the moment: each asks through a port of its own, in its own words, and an adapter of its Infrastructure calls the interface |

The Contract carries no rule and depends on nothing but the shared kernel: the implementation of a question stays in the module that owns it. It is the published language of the context: stable, because others rely on it, and changed with care.

A module that receives a message **translates it into its own model** before using it. A member in Meetings and a payer in Payments are born from the same confirmed account, and each keeps what it needs under its own name.

## Who relates to whom

The relations of the four contexts, as patterns of a context map. An arrow goes from the **upstream** context, which others rely on, to the **downstream** one.

```mermaid
flowchart LR
    A[Accounts]
    M[Meetings]
    P[Payments]
    N[Notifications]

    A -->|Open-host service| M
    A -->|Open-host service| P
    A -->|Open-host service| N
    M <-->|Partnership| P
    M -->|Open-host service| N
    P -->|Open-host service| N

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    class A,M,P,N ctx;
```

*Each box is a context. The label is the pattern of the relation. For Notifications, the contexts hand it facts in the language it publishes: it knows none of them.*

| Relation | Pattern | Why |
| :-- | :-- | :-- |
| Accounts to Meetings, Payments and Notifications | Open-host service | A confirmed account, the access of an account (role, state, subscription), the lock, and the deletion of an account are served the same way to all, whatever the context, in one published language that each context translates into its own model |
| Meetings and Payments | Partnership | A place to pay, a payment that is paid, a payout account chosen for a group: each needs the other, and the two evolve together |
| Accounts, Meetings and Payments to Notifications | Open-host service | Notifications publishes one language to receive what to notify, and knows no context. It is a context like the others, with its own rules ([Notifications](../domain/notifications/description.md)) |

No context is a **conformist**: each one translates what it receives into its own model, in the handler of the fact or in the adapter of the question, and nobody adopts the model of another.

## What each module sends and receives

The tables "What X needs from other contexts" of the domain pages, one for [Meetings](../domain/meetings/description.md#what-meetings-needs-from-other-contexts), [Payments](../domain/payments/description.md#what-payments-needs-from-other-contexts) and [Notifications](../domain/notifications/description.md#what-notifications-needs-from-other-contexts), give each need its exactness and its freshness. This table settles the kind of message for each, with the criterion in the last column. For a fact, "From, to" goes from the sender to the receivers. For a question, it names the module that **owns** the answer first, then the module that asks.

| Need | From, to | Kind | Carries | Why this kind |
| :-- | :-- | :-- | :-- | :-- |
| An account was confirmed, with its role | Accounts to Meetings, Payments | Fact | State | Never lost, never twice. A short delay is fine, and the person is not served before it ends |
| The access of an account: its role, its state and its subscription | Accounts to every module | Question | – | Exact at every action, and never kept by the asker: an identity or a right that is out of date is a risk. Accounts alone decides |
| An account becomes locked | Accounts to Meetings | Fact | Signal | The hand-over that ends: never lost. The sign-ups that close need no fact, since Meetings reads the state of the owner at each sign-up |
| An account is deleted | Accounts to Meetings, Payments, Notifications | Fact | Signal | Each context erases or anonymises its part: the member leaves the groups, the payer is anonymised, the inbox is deleted. Never lost, and nothing is sent back: the request is completed when the deletion is carried out |
| The open items of a deletion | Accounts to Meetings and Payments | Question | – | Exact at the request and again when it is carried out |
| A payout account is validated, deactivated or reactivated | Payments to Meetings | Fact | State | Whole state and version, replaced if newer. A late value is harmless: a payout account deactivated in Payments lets nobody pay, whatever Meetings knows |
| A payout account is chosen for a group, or removed or replaced | Meetings to Payments | Fact | State | Whole state and version, replaced if newer. Payments refuses any payment for a deactivated one |
| A subscription was started, bought or renewed, with the date until which it is valid | Payments to Accounts and Meetings | Fact | State | Accounts keeps the date and whether one was ever held, as part of the access. The date itself decides, so an old value only removes a right for a while. A renewal must count soon, so the fact must not wait |
| A place awaiting payment, with the amount and the group payout account | Meetings to Payments | Fact | State | Exact and once for each place: without it there is no payment |
| A place is lost, or a meeting is cancelled | Meetings to Payments | Fact | Signal | Prompt: when late, a payment paid after its end is recorded, not refunded |
| A payment is paid, or has expired | Payments to Meetings | Fact | Signal | Never lost, and it may come after the start of the meeting |
| A fact to notify, with its kind and data | Every module to Notifications | Fact | State | One notification for each fact, even when it is handed twice |
| The email address of a member | Accounts to Notifications | Question | – | Exact when the email is sent |

The column **Carries** says what a fact brings: a **signal** says that something happened and names who or what it is about, and the receiver reacts. A **state** carries what the receiver keeps and uses later, so that it never has to ask, see [what a fact carries](#what-a-fact-carries). A state creates a **local view** in the receiver (the subscription in Accounts, a member, a payer), which has the freshness its domain page asks for and follows the rules of [the guarantees](#what-the-guarantees-ask-of-a-module).

One need uses both kinds, for two different reasons: whether an account is locked is read through the **question** about its access when an action is decided, and is a **fact** when another context must react to it afterwards.

## A fact and a domain event

A fact of the Contract is often called an *integration event*. It is not the domain event of the module:

| | Domain event | Fact of the Contract |
| :-- | :-- | :-- |
| Lives in | The Domain of the module | The Contract of the module |
| Read by | The module alone | The other modules |
| Holds | What the aggregate knows, free to change with the model | What the receivers need to know, kept stable |
| Link | Many domain events are never made known outside | Made from a domain event, not copied from it |

The two share a name in the domain pages, since the event storming shows both. The Contract keeps the ones other modules need.

## What a fact carries

A fact is named in the past tense, in the words of the glossary of the module that publishes it, for instance "Account confirmed" or "Payment paid". It carries:

- **an identity of its own**, so that a receiver can tell that it already saw it;
- **when it happened**;
- **for a state, the version of what it describes** in the module that publishes it, a number that grows with each change, so that a receiver can tell which of two facts is the more recent;
- **who or what it is about**, by identity: a member, a meeting, a payment;
- **the data the receivers need**, and nothing that they could ask for. A fact that is only a **signal** carries the identities and nothing more. A fact that **transfers a state** carries the **whole state**, never the change since the last one, and its version, because it could not be asked for at the moment it is needed.

It carries **no instruction**: what the receiver does with it is for the receiver to decide. A state it transfers is written in the language of the Contract, not in the model of the sender.

## What a question returns

A question has a **precise answer, not a copy of the data of the other module**, and its asker keeps a **port of its own** for it, so that the answer is read in the words of the asker, even when the owner's words differ (a payer in Payments is a member in Meetings): "what is the access of this user?", "does this member have an open item?". It reads, and changes nothing. A module that asks this way never learns more than it needs, and cannot use the answer later: the answer was true when it was given.

## What the guarantees ask of a module

A fact is delivered **at least once**, and a receiver may get it **late, twice, or after another fact about the same thing**. D1 and D6 ask for an effect that is **once**, so each module that receives facts must:

- **Ignore a fact it already handled**, by its identity. A repeated fact changes nothing.
- **Not depend on the order** of facts. A receiver that needs an order relies on what the fact says, not on when it arrives, see [stale and out-of-order facts](#stale-and-out-of-order-facts).
- **Not lose a fact when it fails**: it is handled again until it succeeds, and a fact that cannot be handled is made visible, never dropped.
- **React through a command**, with the checks, the rules and the authorization of any other command.

The sender, for its part, must make the fact known **if and only if** the change it tells of is kept: a change without its fact, or a fact without its change, is a lie. This is the subject of the [page on consistency](consistency.md#a-change-and-its-fact).

A question has none of these concerns, but one: its answer is **true at the moment it is given**. When a rule must hold even if the situation changes right after the answer, such as a deletion that is carried out while a host leaves, the answer alone is not enough. That is the subject of the [page on consistency](consistency.md#between-two-modules) too.

## Stale and out-of-order facts

Nothing guarantees that two facts arrive in the order they happened, nor that a copy kept from a fact is still fresh. A fact that transfers a state, and the local view it feeds, are exposed to it. Four measures keep the risk in check:

1. **Carry the whole state, not the change.** "Valid until the 3rd of March" stands alone, while "extended by 30 days" depends on every fact before it. A fact that is lost or late then costs time, never a wrong value.
2. **Carry the version of the source.** Each fact holds the version, in the publishing module, of what it describes. The arrival order proves nothing, the version does.
3. **Replace, do not change.** The receiver keeps `(identity, state, version)` and replaces the whole of it only when the version received is **higher** than the one it holds. A fact that is late, repeated, or overtaken by a newer one changes nothing, so the same rule also covers the duplicate.
4. **Ask when the domain asks for exact.** A copy is for what the domain accepts a short delay on. What must be exact when an action is decided is a [question](#two-kinds-of-message), never a copy.

### Reconciling a view

A fifth measure stands behind these four: the receiver **asks the owner for the state again**, with its version, and applies the answer by the same rule as a fact, replacing its view only if the version is higher. It serves two needs:

- **Catching up.** It corrects what a fact that never came, or a fault, left wrong. A receiver does it on a date, as a command of its own (see [D4](drivers.md#d4-things-happen-because-time-passes)), and counts the corrections: a view that is often corrected means the facts are not arriving, and this must not stay hidden.
- **Starting.** A receiver that is new, or whose view is rebuilt, has not seen the earlier facts and needs the state to start from.

It costs one question in the Contract of the owner, "what is the state of this, and its version?". It applies only to the views whose staleness would hurt: the subscription kept by Accounts, answered by Payments. It does not replace the four measures above.

### Example: the validity of a subscription

Payments makes known the date until which a member's subscription is valid, and Accounts keeps it, with whether one was ever held, as part of the access that Meetings reads to tell whether a group is covered.

| Part | What |
| :-- | :-- |
| Fact | "Subscription validity changed": the member, the date until which it is valid or none, and the version of the subscription in Payments |
| View in Accounts | The member, the date, whether one was ever held, the version |
| Rule | A fact whose version is not higher than the one held is ignored. Otherwise the view is replaced |

Keeping the **latest date** would not do: a subscription can become shorter, when it ends with the deletion of its payer's account and is not refunded, so only the version tells which fact is the latest. A copy that lags behind a renewal shows the earlier date, so it refuses the owner for a moment and never grants more than the truth: a subscription cancelled before its end keeps its date, and a locked account no longer depends on it. The domain accepts this ("without noticeable delay"), and that is why a copy is enough here.

### Example: the lock of an account, read and not copied

The lock of an account is set, then lifted when the request is cancelled. Meetings closes the sign-ups to the meetings with a fee while the owner is locked, and reopens them after. It keeps **no copy** of the lock: at each sign-up it reads the state of the owner from Accounts, so there is no fact to be late, swapped or lost, and nothing to reconcile. This is the rule for what withdraws a right (a lock, a role, a deletion): the access is read at the moment of the action, never kept. The fact of the lock only tells Meetings to react, by ending a hand-over whose candidate is locked.

## No cycles

A module depends on the Contracts of the others, never on their inside, and a Contract depends on the shared kernel only. Meetings and Payments need each other, so each one depends on the other's Contract, and there is still no cycle between Contracts. A tool checks that no module reaches into another beyond its Contract, see [who may depend on whom](modules-and-layers.md#who-may-depend-on-whom).

## Scenarios

Each scenario is a diagram of the messages sent and answered, numbered in their order. A message is a **fact**, a **question**, a **command** that a person or a date gives, or an answer of an **outside system**.

### An account is confirmed

```mermaid
flowchart LR
    Per([Person])
    U[Accounts]
    M[Meetings]
    P[Payments]

    Per -->|"1. command: confirm the account"| U
    U -->|"2. fact: Account confirmed"| M
    U -->|"2. fact: Account confirmed"| P

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000;
    class U,M,P ctx;
    class Per actor;
```

*The fact carries the identity of the account, the role and what each context needs to name the person. Meetings creates a member and Payments a payer, each by a command of its own, once, whatever the number of times the fact arrives. With the Administrator role, nobody has anything to create. See [confirming](../domain/accounts/registration/description.md#confirming).*

### A member signs up to a meeting with a fee

```mermaid
flowchart LR
    Per([Member])
    M[Meetings]
    U[Accounts]
    P[Payments]
    PS{{Payment system}}
    N[Notifications]

    Per -->|"1. command: sign up"| M
    M -->|"2. question: is the owner locked?"| U
    U -->|"3. answer: no"| M
    M -->|"4. fact: Place awaiting payment"| P
    P -->|"5. ask for the payment"| PS
    PS -->|"6. answer: paid"| P
    P -->|"7. fact: Payment paid"| M
    M -->|"8. fact to notify: Place confirmed"| N

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000;
    classDef ext fill:#FF9FF3,stroke:#b8509f,color:#000;
    class M,U,P,N ctx;
    class Per actor;
    class PS ext;
```

*The legend of colours is the one of the other diagrams of this page, with the outside system in pink. Meetings first checks its own data: the group payout account must be active and its payout account validated. Message 4 names that group payout account, and Payments refuses a payment for a deactivated one. Meetings keeps the place awaiting payment (message 4) and does not guarantee it. When the payment system does not answer in time, message 7 is the fact "Payment expired" instead, and Meetings releases the places. The sign-up is refused at message 3 when the owner is locked. A sign-up read just before the lock can still go through: its payment ends by itself long before the deletion, whose final check would refuse it otherwise. See [meeting fees](../domain/payments/meeting-fees/description.md#signing-up-to-a-meeting-with-a-fee).*

### An action is guarded by the access of the person

```mermaid
flowchart LR
    Per([Person])
    M[Meetings]
    U[Accounts]

    Per -->|"1. command: cancel a meeting"| M
    M -->|"2. question: the access of this user"| U
    U -->|"3. answer: role, state, subscription"| M

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000;
    class M,U ctx;
    class Per actor;
```

*Every command and every query of every module starts this way, before anything else (see [commands](use-cases.md#commands)). The module knows which role may do the action and whether it reads or changes. It reads the access from Accounts, then decides, refusing a locked account every action that changes something. See [access](../domain/accounts/description.md#access).*

### A deletion is requested and locks the account

```mermaid
flowchart LR
    Per([Member])
    U[Accounts]
    M[Meetings]
    P[Payments]

    Per -->|"1. command: request the deletion"| U
    U -->|"2. question: open items?"| M
    U -->|"3. question: open items?"| P
    M -->|"4. answer: none, or the list"| U
    P -->|"5. answer: none, or the list"| U
    U -->|"6. fact: Account locked"| M

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000;
    class U,M,P ctx;
    class Per actor;
```

*The request is refused when an open item is listed. Otherwise the account is locked, and Meetings ends a hand-over that names the member as candidate. The sign-ups to the meetings with a fee of their groups close by themselves, since Meetings reads the state of the owner at each one, see [deleting an account](../domain/accounts/account-deletion/description.md#locked-account). The questions are asked again at the end of the grace period.*

### A deletion is carried out

```mermaid
flowchart LR
    T([Date])
    U[Accounts]
    M[Meetings]
    P[Payments]
    N[Notifications]

    T -->|"1. command: carry out the deletion"| U
    U -->|"2. fact: Account deleted"| M
    U -->|"2. fact: Account deleted"| P
    U -->|"2. fact: Account deleted"| N

    classDef ctx fill:#DFE6E9,stroke:#636E72,color:#000;
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000;
    class U,M,P,N ctx;
    class T actor;
```

*The final check of the open items is made when the grace period is over, with the same questions as at the request, and is one decision with the carrying out. Accounts deletes the account and makes the fact known. Each context then does its part by a command of its own, and nothing is sent back: the request is already completed. This is [carrying out the deletion](../domain/accounts/account-deletion/description.md#carrying-out-the-deletion).*

## What stays for later pages

- **The dates that start a command**, and the outside systems whose answers are late: the pages on time and on outside systems.
- **Which technique carries a message**, relays the facts and keeps the identity of those handled (a bus, a table, a call behind a port): the decisions at the end. The domain does not say it, and this page does not decide it. How a change and its fact go together, and how a rule stays absolute across modules, is in [consistency](consistency.md).
