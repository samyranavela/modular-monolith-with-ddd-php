# Reliable messaging

Two small patterns that make a fact **never lost and never twice in its effect** between parts that do not share a transaction.

## Transactional outbox

A part that changes its data and also makes a fact known must do both, or neither. A transaction cannot cover its storage and a message channel, so the fact is **written in the part's own storage, in the same transaction as the change**. A separate **relay** reads what was written, delivers it, and marks it delivered.

| Gives | Costs |
| :-- | :-- |
| The fact exists **if and only if** the change is kept | The fact comes **later**, after the relay has run |
| No transaction across storage and channel | The relay can deliver a fact **more than once**, so the receivers must expect it |

Whoever forgets to write the fact loses it: the writing is made part of the way a command is kept, not left to each use case.

## Idempotent receiver

A channel delivers **at least once**: it prefers to deliver twice than to lose. A receiver that gets the same fact twice must have the effect **once**. It keeps the **identity** of the facts it handled, in the same transaction as the effect, and ignores a fact whose identity it already holds. A fact that fails is handled again, and one that keeps failing is set aside and made visible, not dropped.

For a fact that carries a state, the version does the same job: a fact is applied only if its version is higher than the one held, see [stale and out-of-order facts](../between-modules.md#stale-and-out-of-order-facts).

## In this project

- The sender keeps the change and its fact together, and the receiver keeps the identity of the fact with its effect, see [consistency](../consistency.md#a-change-and-its-fact).
- Which technique does it, a table and a relay or another, is decided in the technical decisions, and the pages do not depend on it.

## Sources

- Chris Richardson, [Pattern: Transactional outbox](https://microservices.io/patterns/data/transactional-outbox.html), and [Pattern: Idempotent Consumer](https://microservices.io/patterns/communication-style/idempotent-consumer.html).
- Gregor Hohpe and Bobby Woolf, *Enterprise Integration Patterns*, 2003: Guaranteed Delivery, Idempotent Receiver.
