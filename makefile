ENV_FILE := ./docker/.env
ENV_FILE_EXAMPLE := $(ENV_FILE).example
EXEC_USER := $(shell grep EXEC_USER $(ENV_FILE) | cut -d '=' -f2)
DOCKER_COMPOSE_FILE := ./docker/docker-compose.yml
DOCKER_COMPOSE := docker compose -f $(DOCKER_COMPOSE_FILE) --env-file $(ENV_FILE)

.PHONY: help

help:	## shows help
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo ""
	@awk 'BEGIN {FS = ":.*##"; printf "\033[36m"} /^[a-zA-Z_-]+:.*?##/ { printf "  %-30s %s\n", $$1, $$2 }' $(MAKEFILE_LIST) | sort
	@echo ""


setting-env:	## creates .env file if not exists
	@if [ ! -f $(ENV_FILE) ]; then cp $(ENV_FILE_EXAMPLE) $(ENV_FILE); fi

setting-echo-server:	## echo host url use in env file
	@echo "http://$(shell grep COMPOSE_PROJECT_NETWORK $(ENV_FILE) | cut -d '=' -f2).2"
	@echo "http://127.0.0.1:$(shell grep NGINX_LOCAL_PORT $(ENV_FILE) | cut -d '=' -f2)"

dc-pull: ## pulls docker images
	$(MAKE) -s setting-env
	$(DOCKER_COMPOSE) pull

dc-build:	## builds docker images
	$(MAKE) -s setting-env
	$(DOCKER_COMPOSE) build

dc-up:	## starts docker containers
	$(MAKE) -s setting-env
	$(DOCKER_COMPOSE) up -d
	$(MAKE) -s setting-echo-server

dc-down:	## stops docker containers
	$(DOCKER_COMPOSE) down --remove-orphans --volumes

dc-restart:	## restarts docker containers
	$(DOCKER_COMPOSE) restart
	$(MAKE) -s setting-echo-server

dc-stop:	## stops docker containers
	$(DOCKER_COMPOSE) stop

php-bash:	## enters php container
	$(DOCKER_COMPOSE) exec -it -u $(EXEC_USER) php bash

php-tinker:	## enters php tinker
	$(DOCKER_COMPOSE) exec -it -u $(EXEC_USER) php php artisan tinker

node-bash:	## enters node container
	$(DOCKER_COMPOSE) exec -it -u $(EXEC_USER) node bash