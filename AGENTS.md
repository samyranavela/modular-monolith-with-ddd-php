# AGENTS.md

Modular monolith in PHP and Symfony, built domain first: Accounts, Meetings, Payments, Notifications in
`src/<Module>/`, plus `SharedKernel`. Modules reach each other only through `Contract`. Persistence and
authentication are not wired yet: ask before adding either.

Everything runs in Docker through the Makefile (`make help`). Never run PHP, Composer or `vendor/bin/*` on the
host: the make scripts set up the environment (`APP_ENV=test`).

Domain language: `GLOSSARY-MAP.md`, `docs/domain/`, `docs/architecture/README.md`.

## How we build

Domain first (DDD, hexagonal), test first (TDD): write code only to turn a red test green. A technical mechanism (bus,
outbox, inbox, persistence) is built when a use case needs it, never ahead of one.

- **Tickets**: one per use case; one too big for a session splits by business rule. The context with the fewest
  dependencies goes first (`docs/domain/README.md`, "How the contexts depend on one another").
- **Slices**: vertical, one use case at a time: Domain, then Application, then Infrastructure or Ui as the use case needs.
- **Tests by layer**: Domain, the maximum of unit tests and a 100% mutation score (`make mutation`). Application, as much
  as possible, whether the use case orchestrates or choreographs. Infrastructure, integration tests. Ui, functional tests.
- **Documentation stays true**: amend `docs/domain/` when a business decision changes; enrich `docs/architecture/` when
  a new concept or pattern arrives; write an ADR when an impactful decision is taken.
- **The board** (GitHub project 2): `docs/agents/project-board.md` says how tickets are structured and how to create,
  start and move one. Move the card to In progress when you start the ticket; the PR with `Closes #n` moves it on.
- **A task**: work it alone, open the PR and stop (no merge, no next ticket). End with the choices that no glossary, ADR
  or line here covered, for review: each one becomes a guideline, an architecture update or a business clarification.

## Agent skills

### Issue tracker

Issues and wayfinder maps live in GitHub Issues (`samyranavela/modular-monolith-with-ddd-php`, `gh` CLI). See `docs/agents/issue-tracker.md`.

### Triage labels

Default vocabulary: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Multi-context: `GLOSSARY-MAP.md` at the root, one `GLOSSARY.md` per module in `src/<Module>/`, ADRs in `docs/adr/` and `src/<Module>/docs/adr/`. See `docs/agents/domain.md`.
