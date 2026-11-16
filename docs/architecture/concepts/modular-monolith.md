# Modular monolith

One application, deployed as a whole, cut into **modules** that are as independent as separate services would be. Each module has a clear boundary, hides its inside, owns its data and is reached only through a small public face.

The cut follows the **bounded contexts** of Domain-Driven Design: a bounded context is the area inside which each word of the language has a single meaning, and where one model applies. The same word may mean different things in two contexts, and each context keeps its own definition in its own glossary.

A modular monolith keeps the benefits of one deployment, such as simple operation and calls that cost nothing, while keeping the boundaries sharp enough that a module could one day be extracted. The boundaries hold only if something checks them, since nothing else stops a shortcut.

## In this project

- A module for each of the five contexts of the [domain](../../domain/README.md#the-five-contexts), see [modules and layers](../modules-and-layers.md#one-module-for-each-context).
- Each module owns its data, and reaches another only through its Contract, see [the contract of a module](../modules-and-layers.md#the-contract-of-a-module).
- The boundaries are checked by a tool, not by habit.

## Sources

- Martin Fowler, [BoundedContext](https://martinfowler.com/bliki/BoundedContext.html), 2014.
- Eric Evans, *Domain-Driven Design: Tackling Complexity in the Heart of Software*, 2003: part IV, strategic design.
- Martin Fowler, [MonolithFirst](https://martinfowler.com/bliki/MonolithFirst.html), 2015.
