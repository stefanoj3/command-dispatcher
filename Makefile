PHPUNITFLAGS=""
ifeq ($(COVREPORT), true)
PHPUNITFLAGS=--coverage-html build/report/phpunit/
endif

ifeq ($(CI), true)
PHPUNITFLAGS=--coverage-clover=coverage.xml
endif

.PHONY: help
## help: prints this help message
help:
	@echo "Usage:"
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed -e 's/^/ /'

.PHONY: phpcs
## phpcs: run phpcs against the codebase
phpcs:
	@echo "Running phpcs ~"
	@docker-compose exec -T php74 ./vendor/bin/phpcs --standard=/app/phpcs-ruleset.xml /app/src /app/tests

.PHONY: phpcbf
## phpcbf: run phpcbf against the codebase
phpcbf:
	@echo "Running phpcs ~"
	@docker-compose exec -T php74 ./vendor/bin/phpcbf --standard=/app/phpcs-ruleset.xml /app/src /app/tests

.PHONY: psalm
## psalm: run psalm against the codebase
psalm:
	@echo "Running psalm ~"
	@docker-compose exec -T php74 ./vendor/bin/psalm

.PHONY: check
## check: run psalm and phpcs against the codebase
check: phpcs psalm

.PHONY: devenv-setup
## devenv-setup: starts the development environment and fetch dependencies
devenv-setup:
	@echo "Starting development environment ~"
	@docker-compose up -d
	@docker-compose exec -T php74 composer install

.PHONY: phpunit74
## phpunit74: run test suite inside the php74 container. To get a test report in the build/ folder set COVREPORT=true - EG COVREPORT=true make phpunit
phpunit74:
	@echo "Running tests in php 7.4"
	@docker-compose exec -T php74 ./vendor/bin/phpunit $(PHPUNITFLAGS)
	@echo "------- DONE -------"

.PHONY: phpunit80
## phpunit80: run test suite inside the php80 container.
phpunit80:
	@echo "Running tests in php 8.0"
	@docker-compose exec -T php80 ./vendor/bin/phpunit
	@echo "------- DONE -------"

.PHONY: phpunit81
## phpunit81: run test suite inside the php80 container.
phpunit81:
	@echo "Running tests in php 8.1"
	@docker-compose exec -T php81 ./vendor/bin/phpunit
	@echo "------- DONE -------"


.PHONY: phpunitall
## phpunitall: run tests in all php containers
phpunitall: phpunit74 phpunit80 phpunit81

.PHONY: shell
## shell: opens a shell in the php container
shell:
	@docker-compose exec php74 sh
