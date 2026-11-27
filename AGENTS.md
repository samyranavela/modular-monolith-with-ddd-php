# AGENTS.md

Modular monolith in PHP and Symfony, built domain first: Accounts, Meetings, Payments, Notifications in
`src/<Module>/`, plus `SharedKernel`. Modules reach each other only through `Contract`. Persistence and
authentication are not wired yet: ask before adding either.

Everything runs in Docker through the Makefile (`make help`). Never run PHP, Composer or `vendor/bin/*` on the
host: the make scripts set up the environment (`APP_ENV=test`).

Rules: `CODING_STANDARDS.md`. Domain language: `GLOSSARY-MAP.md`, `docs/domain/`, `docs/architecture/README.md`.

## Agent skills

### Issue tracker

Issues and wayfinder maps live in GitHub Issues (`samyranavela/modular-monolith-with-ddd-php`, `gh` CLI). See `docs/agents/issue-tracker.md`.

### Triage labels

Default vocabulary: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Multi-context: `GLOSSARY-MAP.md` at the root, one `GLOSSARY.md` per module in `src/<Module>/`, ADRs in `docs/adr/` and `src/<Module>/docs/adr/`. See `docs/agents/domain.md`.
