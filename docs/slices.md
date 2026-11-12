# Build slices

Each slice is one PR, merged before the next. No long-lived module branches.

## Order

1. **S0a**: tooling and docs: Symfony 8.1, php-cs-fixer (`@Symfony`), Deptrac, PHPStan max, Docker Compose, a Makefile (everything runs in containers), GitHub Actions,
   test base (PHPUnit, DAMA, Foundry, messenger-test), README and `docs/`.
2. **S0b**: technical base: `SharedKernel`, three Messenger buses, outbox (per-module table + Scheduler relay),
   inbox/idempotence, generic API Platform Processor/Provider, cross-cutting spikes.
3. Modules: UserAccess → Registrations → Administration → Payments; Meetings has its own slice plan.
4. Final cross-module slices (`CreateMeetingGroup`, meeting fees Meetings ↔ Payments).

## Definition of done (every slice)

- Tests written first (red → green), domain → integration → HTTP.
- One HTTP test per endpoint; traceability lines written with the tests.
- php-cs-fixer, Deptrac and PHPStan max pass; CI is green.
- Module glossary, `docs/api-mapping.md` lines for new endpoints, ADR if a hard decision was made.
- Deviations from the source are decided in the slice and recorded as a "deviation" line in the traceability table.
