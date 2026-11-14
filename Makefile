# Everything runs in the containers: the host needs `make` and Docker, nothing else.
# `make help` lists the targets. Pass arguments to a command with ARGS, e.g. make composer ARGS="require symfony/lock".

# The containers run as the host user, so what they write (vendor, var, recipes) is not owned by root.
export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

COMPOSE := docker compose
# -T: no TTY, so the targets also work in CI and in git hooks.
APP := $(COMPOSE) exec -T app

.DEFAULT_GOAL := help
.PHONY: help build up down logs sh install composer console hooks lint format format-check deptrac stan test qa precommit

help: ## List the targets
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk -F':.*## ' '{printf "  %-14s %s\n", $$1, $$2}'

build: ## Build the images
	$(COMPOSE) build --pull

up: ## Start the stack and wait until it is healthy (and point git at the repo's hooks)
	$(COMPOSE) up --detach --wait
	@$(MAKE) --no-print-directory hooks

down: ## Stop the stack (`make down ARGS=-v` also drops the database)
	$(COMPOSE) down --remove-orphans $(ARGS)

logs: ## Follow the logs
	$(COMPOSE) logs --follow --tail=100 $(ARGS)

sh: ## Open a shell in the app container
	$(COMPOSE) exec app sh

install: ## Install the Composer dependencies
	$(APP) composer install --no-interaction --prefer-dist

composer: ## Run a Composer command: make composer ARGS="require ..."
	$(APP) composer $(ARGS)

console: ## Run a Symfony console command: make console ARGS="about"
	$(APP) php bin/console $(ARGS)

hooks: ## Use the repo's git hooks (.githooks/): the pre-commit runs `make precommit`
	@git config core.hooksPath .githooks

lint: ## Syntax check (php-parallel-lint)
	$(APP) composer lint

format: ## Fix the code style (php-cs-fixer, @Symfony)
	$(APP) composer format

format-check: ## Check the code style without changing files
	$(APP) composer format:check

deptrac: ## Check the module boundaries (Deptrac)
	$(APP) composer deptrac

stan: ## Static analysis (PHPStan, level max)
	$(APP) composer stan

test: ## Run the test suite (PHPUnit)
	$(APP) composer test

qa: ## Every check, as the CI runs them
	$(APP) composer qa

precommit: ## What the pre-commit hook runs: the fast checks
	$(APP) composer lint
	$(APP) composer format:check
	$(APP) composer deptrac
	$(APP) composer symfony
	$(APP) composer stan
