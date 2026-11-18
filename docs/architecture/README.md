# Architecture

The [domain documentation](../domain/README.md) says what the application does. This documentation says how the code answers it: how it is cut into modules, how a request travels through a module, and how the modules work together.

The project is a proof of concept. It sets out to show four styles working together, and to put each to the test against the rules of the domain:

| Style | What it brings here | Concept |
| :-- | :-- | :-- |
| **Hexagonal architecture** | The rules of the domain sit at the centre and know nothing of the outside. Everything that touches the outside, a screen, a database, a payment system, is replaceable. | [Hexagonal architecture](concepts/hexagonal-architecture.md) |
| **Domain-Driven Design** | The four contexts of the domain become four modules, with their own words and their own data. | [Modular monolith](concepts/modular-monolith.md) |
| **CQRS** | Changing something and reading something are two different paths, each with its own model. | [CQRS](concepts/cqrs.md) |
| **Event-driven architecture** | Modules work together through facts that happened, and ask a question only when the domain needs an answer that is right at the moment. | [Event-driven architecture](concepts/event-driven-architecture.md) |

## Order of the pages

The pages go from what the domain demands to what the code does. A technical choice, such as a library or a database, is made last, when the demand it answers is written down.

1. [What the domain demands](drivers.md): the requirements that shape the architecture, taken from the domain pages.
2. [Modules and layers](modules-and-layers.md): how the code is cut and which part may depend on which.
3. [Use cases](use-cases.md): how a request is handled, for a change and for a reading.
4. [Between modules](between-modules.md): what goes from one module to another, in which form and with which guarantees.
5. [Consistency](consistency.md): how a change and its fact go together, how a fact has its effect once, and how a rule stays absolute when two operations meet.

The pages on time, on the outside systems and on reading come next, then the technical choices, each recorded as a decision and tied to the demand it answers.

## Concepts

Each pattern or theory the code relies on has a short page in [`concepts/`](concepts), with the sources it comes from. The architecture pages link to them instead of explaining them again.
