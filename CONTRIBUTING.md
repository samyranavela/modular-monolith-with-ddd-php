# Contributing

## Setup

Everything runs in Docker: you need `make` and Docker, nothing else.

```bash
make build
make up        # also points git at .githooks/, whose pre-commit runs `make precommit` in the containers
make install
```

## Branches and commits

- One branch and one small PR per change, small enough to be reviewed in one sitting; merge before starting the next.
- Commit messages are in English with a body. The first commit of a branch has the subject `[Type] Description`
  (Feat, Fix, Docs, Chore or Refactor; the merge adds the number of the pull request), and a commit refers to its
  ticket in the body (`Refs #n`).

## Before pushing

```bash
make qa
```

## Adding a module or an endpoint

- Module layout: `App\<Module>\{Domain,Application,Infrastructure,Ui,Contract}`; add its layers and ruleset to `deptrac.php`.
- Endpoint: write the tests first, then add the API Platform resource under `Ui\Api\Resource`.

## Review

A review reads the diff against [CODING_STANDARDS.md](CODING_STANDARDS.md), on top of what `make qa` checks.

- **The spec** is the ticket that the commit messages refer to (`Refs #n`, `Closes #n`).
- **The business**: the pages of [docs/domain](docs/domain/README.md), the glossaries and the ADRs of `docs/adr/`.
