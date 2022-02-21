php := php
sy := php bin/console
PROJECT_NAME := app

.DEFAULT_GOAL := help
.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: phpstan
phpstan: ## Analyse code error
	php vendor/bin/phpstan analyse src

.PHONY: phpcs
phpcs: ## Analyse code syntaxe and fixe
	php vendor/bin/php-cs-fixer fix
