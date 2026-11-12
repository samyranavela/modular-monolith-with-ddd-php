# Contributing

## Setup

Everything runs in Docker: you need `make` and Docker, nothing else.

```bash
make build
make up        # also points git at .githooks/, whose pre-commit runs `make precommit` in the containers
make install
```

## Branches and commits

- One branch and one PR per build slice (see `docs/slices.md`); merge before starting the next.
- Commit messages are in English with a body. The first commit of a branch uses `[Type] TICKET - Description`.

## Before pushing

```bash
make qa
```

## Adding a module or an endpoint

- Module layout: `App\<Module>\{Domain,Application,Infrastructure,Ui,Contract}`; add its layers and ruleset to `deptrac.yaml`.
- Endpoint: write the tests first, add the API Platform resource under `Ui\Api\Resource`, and add a line to
  `docs/api-mapping.md`.
