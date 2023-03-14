# Executables (local)
DOCKER_COMPOSE = docker-compose

# Docker containers
PHP_CONTAINER = $(DOCKER_COMPOSE) exec app
REDIS_CONTAINER = $(DOCKER_COMPOSE) exec redis
POSTGRES_CONTAINER = $(DOCKER_COMPOSE) exec db
NGINX_CONTAINER = $(DOCKER_COMPOSE) exec nginx
RABBITMQ_CONTAINER = $(DOCKER_COMPOSE) exec rabbitmq

# Executables
ARTISAN = $(PHP_CONTAINER) php artisan $(c)

# Misc
.DEFAULT_GOAL = help
.PHONY = help setup build up up-d start stop down logs logs-f ps php-bash artisan list-ip-containers force

## 👷 Makefile
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## 🐳 Docker
setup: ## Sets up dependencies for environment
	sh docker/setup.sh

build: ## Builds container(s)
	@$(DOCKER_COMPOSE) build --pull --no-cache $(c)

up: ## Start container(s)
	@$(DOCKER_COMPOSE) up $(c)

up-d: ## Start container(s) in detached mode (no logs)
	@$(DOCKER_COMPOSE) up --detach $(c)

start: setup build up-d ## Set up, build and start the containers

stop: ## Stop container(s)
	@$(DOCKER_COMPOSE) stop $(c)

down: ## Stop and remove container(s)
	@$(DOCKER_COMPOSE) down $(c) --remove-orphans

logs: ## Show logs
	@$(DOCKER_COMPOSE) logs $(c)

logs-f: ## Show live logs
	@$(DOCKER_COMPOSE) logs --tail=0 --follow $(c)

ps: ## Show containers' statuses
	@$(DOCKER_COMPOSE) ps

php-bash: ## Connect to the PHP FPM container via BASH
	@$(PHP_CONTAINER) bash

artisan: force ## Laravel's Artisan
	@$(ARTISAN)

list-ip-containers: ## List all ip containers
	docker network inspect tarefas | grep --color -E 'IPv4Address|Name'

force:
	@true
