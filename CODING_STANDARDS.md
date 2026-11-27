# Coding standards

The reference of a review, read with the diff. What a tool checks is not repeated here: `make qa` and `make mutation` run it. What follows is judgement, and each rule says why.

## Language

### A name of the code is a word of the glossary
The glossary of a module is the language of its code. A class, a method or an event named otherwise opens a second vocabulary, and the words under `_Avoid_` are not used.

## Domain

### A rule of the business lives in the Domain
A rule held by a handler is a rule that the tests of the Domain no longer see, see [use cases](docs/architecture/use-cases.md).

## Modules

### A port goes where its language says
A port that carries a type or a word of one context stays in that module. A port with no language of any context, that every module would write the same way, goes in the shared kernel, see [modules and layers](docs/architecture/modules-and-layers.md).

## Scope

### Code waits for the use case that needs it
An API added before the use case of the ticket that calls it is a finding. The Domain of a ticket may come before its Application, within the same ticket.

## Tests

### A fake stands for a port, not a mock
A fake keeps the behaviour of the port (a repository keeps what is added), so that a test asserts the outcome and not the calls made. It then survives a change in how the use case is written.

## Documentation

### An ADR states only what was decided
Each claim of an ADR can be traced to the owner of the project or to a page of the repository. Anything else is a proposal, written as one.
