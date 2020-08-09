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
	@docker run --rm -v $(PWD):/app --rm cytopia/phpcs:3-php7.4 --standard=/app/phpcs-ruleset.xml /app/src /app/tests

.PHONY: psalm
## psalm: run psalm against the codebase
psalm:
	@echo "Running psalm ~"
	@docker-compose exec -T -u $$(id -u) php ./vendor/bin/psalm

.PHONY: check
## check: run psalm and phpcs against the codebase
check: phpcs psalm

.PHONY: devenv-setup
## devenv-setup: starts the development environment and fetch dependencies
devenv-setup:
	@echo "Starting development environment ~"
	@docker-compose up -d
	@docker-compose exec -T php composer install

.PHONY: phpunit
## phpunit: run test suite inside the php container. To get a test report in the build/ folder set COVREPORT=true - EG COVREPORT=true make phpunit
phpunit:
	@docker-compose exec -T -u $$(id -u) php ./vendor/bin/phpunit $(PHPUNITFLAGS)

.PHONY: shell
## shell: opens a shell in the php container
shell:
	@docker-compose exec php sh
