# Hexagonal architecture

Also called **ports and adapters**. The application is a centre that holds the domain and its use cases, and the outside world reaches it only through **ports**. A port is an interface written in the language of the application. An **adapter** is the code that plugs a particular outside thing into a port.

Two sides exist:

- **Driving side**: whatever starts a use case, such as a person on a screen, a schedule or another module. Its adapters call a port that the application offers.
- **Driven side**: whatever a use case needs, such as storage, a payment system or a clock. The application declares a port, and an adapter implements it.

The aim is that the centre can be run and tested without any outside thing, and that an outside thing can change without touching the centre. The rule that makes it hold is the **dependency rule**: dependencies point inwards, so the domain knows nothing of the layers around it.

## In this project

- Domain and Application are the inside. Ui holds the driving adapters, Infrastructure the driven adapters, see [modules and layers](../modules-and-layers.md).
- The entrance of a module is its driving port, see [use cases](../use-cases.md#the-entrance-of-a-module).
- Outside systems, such as the payment system and the mail, are behind driven ports, see [D5](../drivers.md#d5-outside-systems-are-slow-unreliable-and-sometimes-late).

## Sources

- Alistair Cockburn, [Hexagonal architecture](https://alistair.cockburn.us/hexagonal-architecture/), 2005. The original article.
- Robert C. Martin, [The Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html), 2012. The same idea in concentric layers, with the dependency rule.
