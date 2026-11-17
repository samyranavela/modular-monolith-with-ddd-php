# Modular monolith

One application, deployed as a whole, cut into **modules** that are as independent as separate services would be. Each module has a clear boundary, hides its inside, owns its data and is reached only through a small public face.

The cut follows the **bounded contexts** of Domain-Driven Design: a bounded context is the area inside which each word of the language has a single meaning, and where one model applies. The same word may mean different things in two contexts, and each context keeps its own definition in its own glossary.

A modular monolith keeps the benefits of one deployment, such as simple operation and calls that cost nothing, while keeping the boundaries sharp enough that a module could one day be extracted. The boundaries hold only if something checks them, since nothing else stops a shortcut.

## Ready to be extracted

The point of the boundaries is that a module can be taken out of the application when a reason appears: a load that is not the others', a team of its own, a build that has become too long. What makes it cheap, or not, is how modules talk to each other and where their data lives:

- **A call goes through a port written in the words of the caller.** The caller declares what it needs, an adapter of its infrastructure answers it, and today the adapter calls the other module in the same process. Taking the other module out means replacing that adapter with a client of the network, and nothing inside the caller changes. The adapter also translates, so that the model of the other module never enters the caller.
- **The data is as separate as the code.** Each module has its tables, or its schema, and no other module reads or writes them. A reference to something that belongs to another module is an identity, with no foreign key and no constraint across the two, so that the tables can move to another database without a rewrite.
- **A call is written as if it crossed a network.** It carries what the answer needs in one go, and does not chat: a module that needs ten calls to do one thing cannot be taken out.
- **A shared transaction, a shared table or a join across modules ties them to one database and one process**, and is a shortcut that makes the extraction expensive, see [why not a shared transaction](../consistency.md#why-not-a-shared-transaction).

The way to extract is the same each time: expose the module on the network, replace the adapter that called it, then deploy it apart with a copy of its own data.

## In this project

- A module for each of the four contexts of the [domain](../../domain/README.md#the-four-contexts), see [modules and layers](../modules-and-layers.md#one-module-for-each-context).
- Each module owns its data, and reaches another only through its Contract, see [the contract of a module](../modules-and-layers.md#the-contract-of-a-module).
- The port of the caller and the adapter that answers it are the ones of [between modules](../between-modules.md#the-contract-has-two-parts): a question is asked through a port of the asker.
- No constraint crosses two modules, see [one module for each context](../modules-and-layers.md#one-module-for-each-context).
- The boundaries are checked by a tool, not by habit.

## Sources

- Julien Topçu and Thomas Pierrain, [Le pattern Hive : une stratégie de modularisation pour votre monolithe modulaire ou vos microservices](https://mixitconf.org/en/2025/le-pattern-hive-une-strategie-de-modularisation-pour-votre-monolithe-modulaire-ou-vos-microservice), MiXiT 2025, and [live coding the Hive](https://2026.springio.net/sessions/live-coding-the-hive-building-a-microservices-ready-modular-monolith/), Spring I/O 2026: ports and adapters between modules, one data store for each module, extraction by replacing an adapter.
- Kamil Grzybek, [Modular Monolith: Integration Styles](https://www.kamilgrzybek.com/blog/posts/modular-monolith-integration-styles), 2020: the integration styles of modules and what each costs in coupling.
- Spring Modulith, [Application events](https://docs.spring.io/spring-modulith/reference/events.html): events as the main way for modules to interact, and the cost of a transaction that spans them.

- Martin Fowler, [BoundedContext](https://martinfowler.com/bliki/BoundedContext.html), 2014.
- Eric Evans, *Domain-Driven Design: Tackling Complexity in the Heart of Software*, 2003: part IV, strategic design.
- Martin Fowler, [MonolithFirst](https://martinfowler.com/bliki/MonolithFirst.html), 2015.
