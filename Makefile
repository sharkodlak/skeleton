TARGET := $(firstword $(MAKECMDGOALS))
ARGS := $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))
SERVICE ?= php
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

down: stop ## Alias for stop

exec: ## Open a shell in a container (usage: make exec [service])
	$(COMPOSE) exec $(SERVICE) bash

fix: ## Run code formatter
	$(COMPOSE) exec $(SERVICE) composer cmd:fix

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

qa: ## Run project QA checks
	$(COMPOSE) exec $(SERVICE) composer cmd:qa

up: start ## Alias for start

.PHONY: help build db-migrate db-seed db-status down exec fix in install restart start stop test qa up
