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

down: stop ## Alias for stop

exec: ## Open a shell in a container (usage: make exec [service])
	$(COMPOSE) exec $(SERVICE) bash

fix: ## Run code formatter
	$(COMPOSE) exec $(SERVICE) composer cmd:fix

in: ## Open a shell in a service, same as exec
	@$(MAKE) --silent exec SERVICE=$(SERVICE) $(ARGS)

migrate: ## Run DB migration script
	$(COMPOSE) exec db db/migrations/migrate.sh

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

.PHONY: help build down exec fix in migrate restart start stop test qa up
