# Modular monolith with DDD in PHP / Symfony

A modular monolith written in PHP 8.5 with Symfony 8.1 and API Platform, organized around Domain-Driven Design.
It is a functionally equivalent port of [modular-monolith-with-ddd](https://github.com/kgrzybek/modular-monolith-with-ddd)
(.NET): same five bounded contexts and same business rules, built with the conventions and components of the PHP
ecosystem rather than translated line by line.

## Bounded contexts

| Context | Responsibility |
| :-- | :-- |
| UserAccess | Users, authentication, roles and permissions |
| Registrations | Registration and confirmation of new members |
| Administration | Verification of meeting group proposals |
| Meetings | Meeting groups, meetings, attendees, comments and fees |
| Payments | Price list, subscriptions and meeting fee payments (event sourced) |

## Architecture

- One `src/`, one DI container, no bundle per module. Each module is
  `App\<Module>\{Domain,Application,Infrastructure,Ui,Contract}`; shared code lives in `App\SharedKernel`.
- `Contract` is the only API a module exposes to the others (integration events, read interfaces).
- CQRS on three Messenger buses. Asynchronous flows use a per-module outbox, an inbox and scheduled jobs.
- API only: API Platform with DTO resources (never aggregates), JSON-LD, JWT authentication, permissions through voters.
- Persistence: Doctrine ORM (writes) and DBAL (reads) on PostgreSQL, one schema per module. Payments is event sourced.
- Module boundaries are enforced by Deptrac (`deptrac.yaml`).

## Getting started

Everything runs in Docker: the only tools needed on your machine are `make` and Docker.

```bash
make build
make up        # starts the stack, waits until it is healthy and installs the git hooks
make install   # installs the Composer dependencies in the container
```

The app is served on http://localhost:8000 (mail catcher on port 8025).

## Commands

Run `make help` for the full list.

| Command | Purpose |
| :-- | :-- |
| `make qa` | All checks, as the CI runs them |
| `make lint` | php-parallel-lint (syntax) |
| `make format` / `make format-check` | php-cs-fixer (`@Symfony`) |
| `make deptrac` | Deptrac, module boundaries |
| `make stan` | PHPStan, level max |
| `make test` | PHPUnit |
| `make composer ARGS="..."` / `make console ARGS="..."` | Composer and Symfony console, in the container |

## Documentation

- [`docs/`](docs/README.md): architecture, conventions, test traceability, API mapping
- [`CONTRIBUTING.md`](CONTRIBUTING.md): setup, branches, commits
- [`CODING_STANDARDS.md`](CODING_STANDARDS.md): review reference
- [`GLOSSARY-MAP.md`](GLOSSARY-MAP.md): domain glossaries per bounded context
