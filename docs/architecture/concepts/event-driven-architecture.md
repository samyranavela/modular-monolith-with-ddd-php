# Event-driven architecture

An architecture in which the parts of a system work together through **events**: records that something happened. A part that sees something happen states it, and the parts that care react in their own time. The one that states the event does not know who reacts, nor whether anyone does.

The term covers several different ideas, and it helps to name which one is used:

- **Event notification**: the event only says that something happened, and the receivers ask for more if they need it.
- **Event-carried state transfer**: the event carries the data the receivers need, so they do not have to ask.
- **Event sourcing**: the events are the record of the state itself. This is a different style, not needed for the first two.

## What it gives, and what it costs

| Gives | Costs |
| :-- | :-- |
| The sender does not depend on the receivers | A reaction comes **later**: the system is consistent *eventually*, not at once |
| A new receiver is added without touching the sender | The flow of a scenario is spread over several parts and is harder to follow |
| A part that is down does not stop the others | A message may arrive **late, twice or out of order**: each receiver must expect it |

An event never answers "is it true now?". When a decision needs an answer that is right at the moment, a **question** to the part that owns the data is the fitting tool, and it is paid for with a dependency.

## Guarantees

Most delivery systems promise that a message arrives **at least once**: they prefer to deliver twice than to lose it. Exactly once is not offered between independent parts. What matters is the **effect**: a receiver that can recognise a message it already handled, an **idempotent receiver**, has an effect once, whatever the number of deliveries. Making the change and making the event known as two separate steps is a risk of its own: the change and the event must succeed or fail together.

## In this project

- Modules work together through **facts** and **questions**, see [between modules](../between-modules.md#two-kinds-of-message). A fact is an event that a module makes known to the others, in the past tense, and is not the same thing as a domain event, see [a fact and a domain event](../between-modules.md#a-fact-and-a-domain-event).
- A fact is either a **signal** (identities only, the *event notification* above) or **transfers a state** that the receiver keeps in a local view, see [what a fact carries](../between-modules.md#what-a-fact-carries). It carries no instruction.
- A receiver ignores a fact it already handled and reacts through a command, see [what the guarantees ask of a module](../between-modules.md#what-the-guarantees-ask-of-a-module).
- The facts of the Contract of a module are its **published language**, see [the Contract has two parts](../between-modules.md#the-contract-has-two-parts).

## Sources

- Martin Fowler, [What do you mean by "Event-Driven"?](https://martinfowler.com/articles/201701-event-driven.html), 2017.
- Pat Helland, [Data on the Outside versus Data on the Inside](https://www.cidrdb.org/cidr2005/papers/P12.pdf), CIDR 2005.
- Gregor Hohpe and Bobby Woolf, *Enterprise Integration Patterns*, 2003: Publish-Subscribe Channel, Idempotent Receiver, Guaranteed Delivery.
- Eric Evans, *Domain-Driven Design*, 2003: Bounded Context, Published Language, Open Host Service.
- DDD Crew, [Context Mapping](https://github.com/ddd-crew/context-mapping), and [Domain Message Flow Modelling](https://github.com/ddd-crew/domain-message-flow-modelling).
