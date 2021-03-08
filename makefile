include ./docker/.env
include .env
export

user := $(shell id -u)
group := $(shell id -g)
dc := USER_ID=$(user) GROUP_ID=$(group) docker-compose -f ./docker/docker-compose.yml
dr := $(dc) run --rm
de := docker-compose exec
sy := $(de) php bin/console

ENV				?=prod
COM_COLOR		= \033[0;34m
COLOR_BLUE		= \033[0;36m
COLOR_GREEN		= \033[0;32m
COLOR_RED		= \033[0;31m
COLOR_YELLOW	= \033[0;33m
COLOR_DEFAULT	= \033[m

# If the first argument is "composer"...
ifeq (composer,$(firstword $(MAKECMDGOALS)))
	# use the rest as arguments for "composer"
	COMPOSER_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
	# ...and turn them into do-nothing targets
	#$(eval $(COMPOSER_ARGS):;@:)
endif

# If the first argument is "console"...
ifeq (console,$(firstword $(MAKECMDGOALS)))
	# use the rest as arguments for "console"
	SYMFONY_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
	# ...and turn them into do-nothing targets
	#$(eval $(SYMFONY_ARGS):;@:)
endif


.SILENT:

##---------------------------------------

.DEFAULT_GOAL := help
.PHONY: help

help: ## Affiche l'aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$|(^##)' $(MAKEFILE_LIST) | grep '^makefile:' | awk 'BEGIN {FS=":"}; {printf "\033[32m%-20s\033[0m %s\n", $$2, $$3}' | sed -e 's/\[32m##/[33m/' | sed 's/## //g'

##--------------- Docker ----------------
.PHONY: buildDocker
buildDocker: ## (Re) build les images docker
	$(dc) build

.PHONY: startDocker
startDocker: ## Lance l'environnement de développement avec Docker
	echo "$(COLOR_RED)##$(COLOR_DEFAULT) Démarrage de l'environnement docker"
	$(dc) -p $(PROJECT_NAME) up -d --no-recreate

.PHONY: stopDocker
stopDocker: ## Arrête l'environnement de développement Docker
	echo "$(COLOR_RED)##$(COLOR_DEFAULT) Arret de l'environnement docker"
	$(dc) -p $(PROJECT_NAME) down --volumes

.PHONY: enterDocker
enterDocker: ## Entre dans le container "principal" pour y éxecuter des commandes système
	docker exec -it CR_MAGIC_PHP /bin/bash


##------------ Installation -------------
.PHONY: install
install: public/assets vendor/autoload.php ## Installe les différentes dépendances

vendor/autoload.php: composer.lock ## Installe les dépendances PHP
	$(dr) --no-deps php composer install
	touch vendor/autoload.php

node_modules/time: package-lock.json ## Installe les dépendances JS
	$(dr) --no-deps nodejs npm install
	$(dr) --no-deps nodejs npm audit fix
	touch node_modules/time

public/assets: node_modules/time
	$(dr) --no-deps nodejs npm run build

##---------- Commandes de dev -----------
.PHONY: composer
composer: ## Lance la commande "composer"
	$(dr) php composer $(COMPOSER_ARGS)

.PHONY: console
console: ## Lance la commande "bin/console" de Symfony
	$(dr) php bin/console $(SYMFONY_ARGS)

.PHONY: npm
npm: ## Lance la commande "npm"
	$(dr) nodejs npm $(NPM_ARGS)

##-------------- Le Cache ---------------
.PHONY: clearCacheDev
clearCacheDev: ## Purge le cache pour l'environnement de DEV
	echo "$(COLOR_GREEN)##$(COLOR_DEFAULT) Purge du cache $(COLOR_RED)DEV$(COLOR_DEFAULT)"
	$(dr) php bin/console cache:clear --env=dev

.PHONY: clearCacheProd
clearCacheProd: ## Purge le cache pour l'environnement de PROD
	echo "$(COLOR_GREEN)##$(COLOR_DEFAULT) Purge du cache $(COLOR_RED)PROD$(COLOR_DEFAULT)"
	$(dr) php bin/console cache:clear --env=prod

.PHONY: clearCacheAll
clearCacheAll: clearCacheDev clearCacheProd## Purge le cache pour l'environnement de DEV et de PROD

##---------- Mise en production ---------
.PHONY: genProd
genProd: clearCacheProd genAssetsProd ## Prépare le projet pour la prod

##---------------------------------------

