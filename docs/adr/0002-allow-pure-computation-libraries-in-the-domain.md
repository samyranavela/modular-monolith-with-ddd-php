---
status: accepted
date: 2026-11-27
---

# Allow libraries of pure computation in the domain

## Context and Problem Statement

`docs/architecture/modules-and-layers.md` says that the domain "depends on nothing outside itself", and `deptrac.php` enforces it: the Domain layer of a module reaches only itself and the domain part of the shared kernel. The domain does use what PHP itself offers (`filter_var`, `DateTimeImmutable`, `mb_*`), but PHP has no native type for a UUID. `Account` now needs an identity (`AccountId`, see [ADR 0001](0001-confirmation-link-as-a-signed-uri.md)). Must the domain rebuild what `symfony/uid` already does well, to stay free of any library?

## Decision Drivers

* The domain must express the business and stay free of infrastructure: persistence, framework plumbing, input and output.
* Do not rebuild what is not the core of the business (the idea behind a generic subdomain).
* An identifier needs more than a format check: a canonical form, a version, the binary and base32 forms a database wants, and generation. These are easy to get wrong by hand.
* A rule that is applied dogmatically costs more than it protects, but a rule without limit protects nothing.

## Considered Options

* Allow libraries of pure computation in the domain, listed one by one
* Allow no library in the domain: a regular expression for the format and a generator port
* Allow any library in the domain

## Decision Outcome

Chosen option: "Allow libraries of pure computation in the domain, listed one by one", because it keeps what the rule is for, a domain free of infrastructure, without making the domain rebuild a standard tool.

A library qualifies when it does a **pure computation**: no input or output, no state kept, no tie to the framework or to persistence. It is **listed** in `deptrac.php`, so that every addition is visible in a review.

The first library is `symfony/uid`, for `AccountId`, whose value is a `Uuid`, with UUID v7 for the ids it generates: it sorts by creation time, which suits database indexes, and it is a standard (RFC 9562) that databases and tools understand.

### Consequences

* Good, because the domain does not rebuild validation, canonical form and generation of identifiers, and the next identifiers (a member, a payer) use the same tool.
* Good, because the exception is narrow and written down: a criterion and a list.
* Bad, because the domain depends on a package it does not own: its updates reach the domain.
* Bad, because the criterion "pure computation" is a judgement, and is applied by whoever reviews.

### Confirmation

`make deptrac` fails when the domain uses a library that is not in the list of `deptrac.php`. A review checks that a new entry in the list meets the criterion. Once this ADR is accepted, `modules-and-layers.md` says what the domain may depend on, in place of "nothing outside itself".

## Pros and Cons of the Options

### Allow libraries of pure computation in the domain, listed one by one

* Good, because the tool is the standard one, tested by many users.
* Good, because the rule keeps its purpose: no infrastructure in the domain.
* Neutral, because it adds a package and a line in `deptrac.php`.
* Bad, because "pure" needs a judgement for the next library.

### Allow no library in the domain: a regular expression for the format and a generator port

* Good, because the domain depends on nothing.
* Good, because the rule stays as it is written.
* Bad, because a regular expression checks the format only: canonical form, version and database representations are left to rebuild.
* Bad, because a generator port and its adapter are written to hide a pure computation, which brings no isolation from anything that varies.

### Allow any library in the domain

* Good, because it is the least ceremony.
* Bad, because there is no criterion left: whatever library is convenient enters the domain, and the rule protects nothing.

## More Information

The questions about pure computation against infrastructure come from the exchange that led to this ADR. The article [DDD was never the problem, your rules were](https://blog.ecotone.tech/ddd-was-never-the-problem-your-rules-were/) argues for the same pragmatism. This ADR is about libraries only: where the mapping of the persistence lives (attributes on the aggregate or elsewhere) is not decided here, and is decided with the persistence, which is not wired yet.

When this ADR is accepted, the change is made in this order: `composer require symfony/uid`, the `deptrac.php` rule, the amendment of `modules-and-layers.md`, then `AccountId`.
