# Coding standards

Review reference for agents and reviewers. Each rule has an identifier to cite in reviews and is either
**[tool]** (enforced by Deptrac, php-cs-fixer, PHPStan (incl. custom rules in `tools/PHPStan`)) or **judged** (reviewer's call).
A judged rule that becomes checkable moves to **[tool]**. Rules cite ADRs; they do not duplicate them.

## Domain (strict)

- **DOM-1 [tool]** `Domain` depends only on its own namespace and `SharedKernel\Domain` (Payments: also Patchlevel); no Symfony, Doctrine or API Platform. Deptrac.
- **DOM-2 [tool]** `Domain` never depends on `Infrastructure`. Deptrac.

- **DOM-3 [tool]** `SharedKernel.Domain` depends on nothing; the rest of `SharedKernel` only on it, Messenger, Validator and the framework. Deptrac.

## Application

- **APP-1 [tool]** `Application` depends on its own `Domain`, `SharedKernel` and other modules' `Contract` only. Deptrac.
- **APP-2 judged** Handlers publish domain events explicitly; aggregates stay pure.

## Ui / Infrastructure

- **UI-1 [tool]** `Ui` depends on its own `Application` only, never `Domain` or `Infrastructure`. Deptrac.
- **UI-2 judged** API Platform resources are DTOs, never aggregates.

## Cross-module

- **MOD-1 [tool]** Modules reach each other only through `Contract`. Deptrac.
- **MOD-2 [tool]** `Contract` classes are `final readonly`. PHPStan rule `ContractRule`.

## Tests

- **TST-1 judged** Tests are written first; each source test has a traceability line.
- **TST-2 [tool]** PHPStan level max passes on `src` and `tests`.
