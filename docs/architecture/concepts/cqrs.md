# CQRS

**Command Query Responsibility Segregation**: changing something and reading something are two separate paths, each with a model fitted to its job. It extends an older principle, **Command Query Separation**, which says that a method either changes the state or answers a question, never both.

- A **command** expresses an intention to change the state. It goes through the model that holds the rules, and may be refused.
- A **query** asks for data. It changes nothing and reads a model shaped for what is shown.

Writing needs a graph of objects that protects the rules, and reading needs flat data, filtered and sorted. Separating them lets each be simple. The cost is that there are two models to keep consistent, and CQRS is not worth it for a simple part of a system.

CQRS does not require two databases, nor events, nor an asynchronous read side. These are options on top of it, to weigh against the freshness each reading needs.

## In this project

- A use case is a command or a query, see [use cases](../use-cases.md#two-kinds).
- A command goes through the aggregates, a query reads a read model, see [commands](../use-cases.md#commands) and [queries](../use-cases.md#queries).
- A command may give back the identity of what it created, which a strict reading of the principle forbids. The identity is needed at once, and nothing else is returned.

## Sources

- Martin Fowler, [CQRS](https://martinfowler.com/bliki/CQRS.html), 2011.
- Greg Young, [CQRS Documents](https://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf), 2010.
- Bertrand Meyer, *Object-Oriented Software Construction*, 1988: Command Query Separation.
- Microsoft, [CQRS pattern](https://learn.microsoft.com/en-us/azure/architecture/patterns/cqrs), Azure Architecture Center.
