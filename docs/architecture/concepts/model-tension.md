# Model tension

What happens when **one concept serves two responsibilities**. It was written for one of them, and the second bends it, adds to it or constrains it, until it fits neither well. The word for this is a **model tension**, and the usual answer is to **duplicate the concept and specialise each copy**, not to share it.

## How a tension shows

A concept is born for one use. Later, a new behaviour needs the same concept for another purpose, and it is tempting to reuse what exists. Each reuse asks a small change, and the changes pull in different directions:

- **A field that means two things.** A list of fares is what a search shows to choose from. In a booking, the same structure should hold the one fare that was chosen. Using the list in a booking means reading its first element and hoping it is never empty.
- **A rule that is not the same for both.** A schedule must lie in the future when a search looks for trains. A booking that is already finalised may hold a past schedule. One invariant cannot serve both.
- **A behaviour that belongs to one side.** Computing the tax of a price matters to the booking only. Added to the shared price, it pollutes the search with a rule it has no use for.
- **A word that drifts.** A fare is an option in a search and a selection in a booking. Same structure, different meaning: the name has to change with the meaning.

None of this is a matter of code size. The two uses have different reasons to change, and the shared concept ties them together.

## Duplicate and specialise

The answer is to give each side its own version of the concept, with its own name, its own shape and its own rules:

- **The copies differ by what each side needs.** The search keeps its list of fare options, the booking keeps one selected fare, and only the booking knows how a tax is computed.
- **The rules follow the state of what owns them.** An invariant that depends on a state moves up to the aggregate that has that state: a booking is valid either when it is finalised or when every departure is still ahead, which is a rule of the booking and not of the schedule.
- **Duplication is not the cost it looks like.** Code that looks the same but means something else will not stay the same. Copying keeps the two models free to change, and the tests are written for each side's rules.

## The shared kernel as a detector

A shared kernel holds the concepts that are **exactly the same** for every module that uses them: the same meaning, the same structure and types, the same invariants and the same business rules. It is a way to avoid duplicating what is truly identical.

It is also a **detector**. Each new behaviour is first written the simple way. If the simple way forces a change in the kernel that breaks one of those four conditions, the concept is not shared after all. This is the signal to duplicate and specialise, and to take it out of the kernel. What remains after a few rounds is the concept that really is the same everywhere, and it is often a **generic subdomain** such as money, which is better taken from a library than written and shared.

Three rules keep the kernel honest:

- **It is purely business.** It holds no manager, no utility and no technical helper.
- **Every module that uses it must know how the others use it**, since a change touches all of them. This is why it is not shared between teams: the cost is one team's mental load added to another's.
- **It stays small**, and sometimes ends up empty.

## Crossing the border between the two models

Once two modules have their own models, a use case that needs both must translate. The pattern is the **anticorruption layer**:

- The module that needs something declares a **port in its own words**, and its own domain depends on nothing else.
- An adapter, **outside the two domains**, implements that port by calling the other module and **translates its model** into the one of the asker. A layer that lives inside one of the two models gets contaminated by the other, and no longer protects anything.
- A reference to the other module is its **identity**, nothing more: a booking keeps the identifier of the search, not the search.
- The adapter is where a state is checked before a conversion: a selection must be complete before it becomes a booking.
- After the conversion, the asker **owns its copy**. What was true of the other module at the moment of the question no longer matters to it, and the two have different lifetimes: a search lasts a few minutes, a booking months.

## What it does not do

A port with a check and a conversion keeps the models apart. It does **not** make a rule absolute across the two modules: between the question and the action, the other module can change. A rule that must hold at the same moment in two modules asks for more, see [consistency](../consistency.md#ways-to-hold-the-rule).

Separating the models also does not mean separating the deployments. Two modules can stay in one application until a business reason asks for more, such as a different load or a different rhythm of release. Cutting the runtime before the models are apart gives a distributed monolith, where every method call became a network call and nothing was gained, see [ready to be extracted](modular-monolith.md#ready-to-be-extracted).

## In this project

- Each context keeps its own definition of a word in its own glossary, which is the same idea at the level of the language, see [modular monolith](modular-monolith.md).
- A module keeps another module's thing as an **identity**, and a question is asked through a **port of the asker** answered by an adapter that translates, see [between modules](../between-modules.md#the-contract-has-two-parts) and [one module for each context](../modules-and-layers.md#one-module-for-each-context).
- The **shared kernel** is small and carries no rule of the domain, see [one module for each context](../modules-and-layers.md#one-module-for-each-context).

## Sources

- Julien Topçu and colleagues, *Model Mitosis: stop making the wrong choice between microservices and monolith*, [English](https://youtu.be/VO6BSb52K5g) and [French](https://youtu.be/HNQJW5iMZgQ) recordings: model tension, duplicate and specialise, the shared kernel as a heuristic, and the anticorruption layer kept outside both domains.
- Eric Evans, *Domain-Driven Design: Tackling Complexity in the Heart of Software*, 2003: chapter 14, *Maintaining Model Integrity*, on the shared kernel and the anticorruption layer.
- Martin Fowler, [BoundedContext](https://martinfowler.com/bliki/BoundedContext.html), 2014.
