# AGENTS.md

A modular monolith in PHP and Symfony, built domain first: Accounts, Meetings, Payments and Notifications, each in
`src/<Module>/`. `composer.json` and `symfony.lock` give the installed versions and recipes. Persistence and
authentication are not wired yet: ask before adding either.

## Where things are

- Layout: `App\<Module>\{Domain,Application,Infrastructure,Ui,Contract}`, plus `App\SharedKernel`. Modules reach each
  other only through `Contract`. `Domain` has no Symfony, Doctrine or API Platform.
- Rules: `CODING_STANDARDS.md`. Each rule names the tool that enforces it; Deptrac holds the boundaries (`deptrac.php`).
- Domain language and decisions: `GLOSSARY-MAP.md`, `src/<Module>/GLOSSARY.md`, `docs/domain/`. How the code answers
  them: `docs/architecture/README.md`.
- HTTP: API Platform, with DTO resources in `Ui\Api\Resource`.

## Commands

Everything runs in Docker through the Makefile: `make help` lists the targets (`make up`, `make test`, `make qa`,
`make format`). Run Composer and the console through it (`make composer ARGS=...`, `make console ARGS=...`), never on
the host and never `vendor/bin/*` directly: the scripts set up the environment (`APP_ENV=test` for the tests). Run some
tests with `make composer ARGS="test -- --filter Name"`. The pre-commit hook (`.githooks/`) runs `make precommit`.

## Symfony

- Add a capability with `composer require <package>` and let the Flex recipe wire it; do not hand-edit
  `config/bundles.php`.
- Outside `Domain`, follow the Symfony best practices: attributes for framework metadata, autowiring, thin controllers.
- If Doctrine ORM is installed, schema changes go through migrations (`make console ARGS=make:migration`, then
  `doctrine:migrations:migrate`), never `doctrine:schema:update` or hand-written SQL.
- `.env` is committed and holds defaults only; secrets go in `.env.local` (git-ignored) or the secrets vault, read
  through `%env(...)%`.
- Look framework APIs up in the project, not from memory: `make console ARGS="debug:container"`, `debug:autowiring`,
  `config:dump-reference`, and the source under `vendor/`.

## Tests and style

A feature is done when a test exercises it the way a caller would, an HTTP request for a controller or a service call
for a service, not just "it didn't throw". Code style is the `@Symfony` php-cs-fixer ruleset: `make format` fixes it,
`make format-check` checks it.

## Agent skills

### Issue tracker

Issues and wayfinder maps live in GitHub Issues (`samyranavela/modular-monolith-with-ddd-php`, `gh` CLI). See `docs/agents/issue-tracker.md`.

### Triage labels

Default vocabulary: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Multi-context: `GLOSSARY-MAP.md` at the root, one `GLOSSARY.md` per module in `src/<Module>/`, ADRs in `docs/adr/` and `src/<Module>/docs/adr/`. See `docs/agents/domain.md`.
