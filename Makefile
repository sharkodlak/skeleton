TARGET := $(firstword $(MAKECMDGOALS))
ARGS := $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))
SERVICE ?= php
## Forward Xdebug settings from the host into the container, e.g.
## `XDEBUG_MODE=coverage make qa-fast`.
XDEBUG_ENV := $(strip $(if $(XDEBUG_MODE),-e XDEBUG_MODE=$(XDEBUG_MODE),) $(if $(XDEBUG_TRIGGER),-e XDEBUG_TRIGGER=$(XDEBUG_TRIGGER),))
COMPOSE := $(shell \
	if command -v podman-compose >/dev/null 2>&1; then \
		printf '%s' podman-compose; \
	elif command -v podman >/dev/null 2>&1 && podman compose version >/dev/null 2>&1; then \
		printf '%s' 'podman compose'; \
	elif command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then \
		printf '%s' 'docker compose'; \
	else \
		printf '%s' docker-compose; \
	fi \
)

ifneq ($(filter exec in,$(TARGET)),)
$(eval $(ARGS):;@:)
#previous line can't start with tab
	SERVICE := $(if $(ARGS),$(firstword $(ARGS)),$(SERVICE))
	ARGS := $(wordlist 2, $(words $(ARGS)), $(ARGS))
endif

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build the project containers without cache
	$(COMPOSE) build --no-cache

db-migrate: ## Run Phinx migrations
	$(COMPOSE) exec $(SERVICE) composer cmd:db:migrate

db-seed: ## Run Phinx seeds
	$(COMPOSE) exec $(SERVICE) composer cmd:db:seed

db-status: ## Show Phinx migration status
	$(COMPOSE) exec $(SERVICE) composer cmd:db:status

db-create-test: ## Create the test database (idempotent)
	$(COMPOSE) exec db create-test-db.sh

db-migrate-test: ## Run Phinx migrations against the test database
	$(COMPOSE) exec $(SERVICE) composer cmd:db:migrate:test

db-seed-test: ## Run Phinx seeds against the test database
	$(COMPOSE) exec $(SERVICE) composer cmd:db:seed:test

db-status-test: ## Show Phinx migration status of the test database
	$(COMPOSE) exec $(SERVICE) composer cmd:db:status:test

db-setup: db-migrate db-create-test db-migrate-test ## Migrate both the development and the test database

down: stop ## Alias for stop

exec: ## Open a shell in a container (usage: make exec [service])
	$(COMPOSE) exec $(SERVICE) bash

fix: ## Run code formatter
	$(COMPOSE) exec $(XDEBUG_ENV) $(SERVICE) composer cmd:fix

in: ## Open a shell in a service, same as exec
	@$(MAKE) --silent exec SERVICE=$(SERVICE) $(ARGS)

install: ## Create .env from .env.example (if missing) and start the stack
	@test -f .env || cp .env.example .env
	@$(MAKE) --silent start

restart: ## Restart the stack
	$(COMPOSE) restart

start: ## Start containers in background
	$(COMPOSE) up --detach

stop: ## Stop containers
	$(COMPOSE) down

test: qa ## Alias for qa

qa: ## Run the full QA suite (currently the same as qa-slow)
	$(COMPOSE) exec $(XDEBUG_ENV) $(SERVICE) composer cmd:qa

qa-fast: ## Run fast QA checks (lint, phpcs, phpstan, parallel unit tests)
	$(COMPOSE) exec $(XDEBUG_ENV) $(SERVICE) composer cmd:qa:fast

qa-mid: ## Run fast QA checks plus integration tests
	$(COMPOSE) exec $(XDEBUG_ENV) $(SERVICE) composer cmd:qa:mid

qa-slow: ## Alias for qa
	$(COMPOSE) exec $(XDEBUG_ENV) $(SERVICE) composer cmd:qa:slow

up: start ## Alias for start

.PHONY: help build db-create-test db-migrate db-migrate-test db-seed db-seed-test db-setup db-status db-status-test down exec fix in install restart start stop test qa qa-fast qa-mid qa-slow up
