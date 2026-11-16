# Use cases

A **use case** is something that a person, a date or a fact asks the application to do or to show. Every line of a [domain page](../domain/README.md#a-journey-through-the-contexts) that changes or shows something is a use case. This page says how one is handled. It answers D3 and D7 of [what the domain demands](drivers.md).

Concepts used: [CQRS](concepts/cqrs.md) and [hexagonal architecture](concepts/hexagonal-architecture.md).

## Two kinds

A use case is **either** a command **or** a query, never both:

| | Command | Query |
| :-- | :-- | :-- |
| Does | Changes the state of the module | Shows the state, changes nothing |
| Goes through | The aggregates, which hold the rules | A read model, shaped for what is shown |
| May be refused for | A permission, an invalid input, a rule of the domain | A permission, or an item that is not visible to the person |
| Answers | Done, or refused with a reason. It may give back the identity of what it created | The data asked for |

The state of a module changes **only** through a command. This holds as much for what a person does as for what time or another module sets off.

## Where a use case comes from

| Trigger | Example | Through |
| :-- | :-- | :-- |
| A person | A member proposes a meeting group | Ui |
| A date | A registration expires, a payment waiting times out | Ui, as a driving adapter that starts the command on the date |
| A fact of another context | A confirmed registration creates a member | Contract |
| An answer of an outside system | The payment system answers that an amount was paid | Infrastructure, behind a port, and then the use case |

A use case started by a fact or by a date is a command like the others, and has its own permission and its own rules. This makes the chain of an [event storming](../domain/meetings/group-creation-event-storming.md), a command, an event, a policy, then another command, readable in the code.

## The entrance of a module

A module offers its use cases through **one entrance**: it takes a command or a query and answers. A person's screen, a date and another module's fact all reach a use case through it, so that nothing else of the module is visible.

The entrance is the **driving port** of the module. It keeps the adapters of Ui thin: they translate what comes from outside into a command or a query and translate the answer back. They hold no rule.

## Commands

A command is handled in the same steps, each in its own part:

1. **Permission.** The module checks that the person's role grants the permission of the use case, before anything else. UserAccess answers, and a locked account is refused any command that changes something, except the four it keeps. The permission, declared by the module, says whether it reads or changes. See [roles and permissions](../domain/useraccess/description.md#permissions).
2. **Validation of the input.** What is missing or malformed is refused at once, before the domain is reached. This is the form of the input, not a rule of the domain.
3. **Loading.** The use case loads the aggregates it needs, through a port.
4. **Deciding.** The aggregate applies its rules and changes itself, or refuses with the reason of the domain. The rules live there, and nowhere else.
5. **Keeping.** The use case saves what changed, through a port.
6. **Making known.** The facts that happened in the domain are made known, inside the module and to the modules that need them, and the notifications to hand to Notifications are given.

Steps 5 and 6 belong together, since a change and the facts that tell of it must not be separated. This is the subject of the page on consistency.

A use case is short: it loads, asks the aggregate to act and saves. A rule that sits in a use case rather than in the domain is a rule that the tests of the domain no longer see.

A command may give back the **identity** of what it created, since the person needs it at once to go on. It gives back nothing else: what it did is read afterwards with a query.

## Refusals

Three kinds of refusal exist, and each has its own answer:

| Refusal | Because | Example |
| :-- | :-- | :-- |
| **Not allowed** | The role has not the permission, or the account is locked | A locked member who tries to sign up |
| **Not valid** | The input is incomplete or malformed | A proposal without a name |
| **Against a rule** | The domain says no | Accepting a second proposal of a free-plan member |

A refusal against a rule uses the words of the domain and carries the reason that the domain page gives, such as the reason of a rejection. A refusal never changes the state.

## Queries

A query does not go through the aggregates. It reads a **read model**, a view shaped for what is shown, and answers with exactly the data of the screen. This lets a reading be filtered, sorted and counted without the cost of the rules of writing, and it keeps the aggregates small. See [D7](drivers.md#d7-reading-has-its-own-demands).

Two things apply to every query:

- **Permission and visibility.** The query checks the permission, then shows only what the person may see: the group members' list only to group members, the waitlist only to hosts, owner and organizers. An item that is not visible is treated as if it did not exist.
- **Freshness.** A read model has the freshness its domain page asks for. How a read model is kept up to date is the subject of a later page.

## A worked example: accepting a proposal

The page [creating a meeting group](../domain/meetings/group-creation.md) holds three use cases that follow one another.

| Use case | Kind | Trigger | Permission | What it does |
| :-- | :-- | :-- | :-- | :-- |
| Accept a proposal | Command | An administrator, through Ui | Accept proposals, granted to the Administrator role | The proposal decides: it is accepted once, and the free-plan rule is checked. It keeps who decided and when |
| Create the meeting group | Command | The fact that the proposal was accepted | None of a person: the module starts it itself | The group is created from the proposal, with the proposer as owner and member |
| Notify the proposer | A fact handed to Notifications | The same acceptance | | Notifications gives the proposer one notification, see [D6](drivers.md#d6-one-fact-one-notification) |

And, to read the result:

| Use case | Kind | Permission | Shown |
| :-- | :-- | :-- | :-- |
| List my proposals | Query | List my proposals, granted to the Member role | The member's own proposals with their status and, for a rejected one, the reason |
| List all proposals | Query | List all proposals, granted to the Administrator role | Every proposal, filtered by status |

Each use case is named in the words of the domain, and can be traced back to the page that specifies it.
