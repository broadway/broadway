include .env
export

sources = bin/console config src
version = $(shell git describe --tags --dirty --always)
build_name = application-$(version)
# use the rest as arguments for "run"
RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
# ...and turn them into do-nothing targets
#$(eval $(RUN_ARGS):;@:)

.PHONY: fix-permission
fix-permission: ## fix permission for docker env
	sudo chown -R $(shell whoami):$(shell whoami) *
	sudo chown -R $(shell whoami):$(shell whoami) .docker/*

.PHONY: build
build: ## build environment and initialize composer and project dependencies
	docker build --build-arg CI_SERVICE_NAME=$(CI_SERVICE_NAME) .docker/php8.0-dev/ -t gitlab.micro-moduletech.net:5001/micro-module/dev/shared/modules/package-$(CI_SERVICE_NAME)/php8.0-dev:$(CI_COMMIT_REF_SLUG)
	docker-compose build
	docker-compose run --rm --no-deps php sh -lc 'composer install'

.PHONY: stop
stop:
	docker-compose stop

.PHONY: composer-install
composer-install: ## Install project dependencies
	docker-compose run --rm --no-deps php sh -lc 'composer install'

.PHONY: composer-update
composer-update: ## Update project dependencies
	docker-compose run --rm --no-deps php sh -lc 'composer update'

.PHONY: composer-outdated
composer-outdated: ## Show outdated project dependencies
	docker-compose run --rm --no-deps php sh -lc 'composer outdated'

.PHONY: composer-validate
composer-validate: ## Validate composer config
	    docker-compose run --rm --no-deps php sh -lc 'composer validate --no-check-publish'

.PHONY: composer
composer: ## Execute composer command
	docker-compose run --rm --no-deps php sh -lc "composer $(RUN_ARGS)"

.PHONY: phpunit
phpunit: ## execute project unit tests
	docker-compose run --rm php sh -lc  "./vendor/bin/phpunit $(conf)"

.PHONY: phpstan
phpstan: ## phpstan - PHP Static Analysis Tool
	docker-compose run --rm --no-deps php sh -lc './vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests'

.PHONY: psalm
psalm: ## psalm is a static analysis tool for finding errors in PHP applications
	docker-compose run --rm --no-deps php sh -lc './vendor/bin/psalm --config=psalm.xml'

style: phpstan psalm ## executes php analizers

.PHONY: lint
lint: ## checks syntax of PHP files
	docker-compose run --rm --no-deps php sh -lc './vendor/bin/parallel-lint ./ --exclude vendor --exclude bin/phpunit'

.PHONY: logs
logs: ## look for service logs
	docker-compose logs -f $(RUN_ARGS)

.PHONY: help
help: ## Display this help message
	    @cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: php-shell
php-shell: ## PHP shell
	docker-compose run --rm php sh -l

unit-tests: ## Run unit-tests suite
	docker-compose run --rm php sh -lc 'vendor/bin/phpunit --testsuite broadway'

static-analysis: style coding-standards ## Run phpstan, deprac, easycoding standarts code static analysis

coding-standards: ## Run check and validate code standards tests
	docker-compose run --rm --no-deps php sh -lc 'vendor/bin/ecs check src tests'
	docker-compose run --rm --no-deps php sh -lc 'vendor/bin/phpmd src/ text phpmd.xml'

coding-standards-fixer: ## Run code standards fixer
	docker-compose run --rm --no-deps php sh -lc 'vendor/bin/ecs check src tests --fix'

security-tests: ## The SensioLabs Security Checker
	docker-compose run --rm --no-deps php sh -lc 'vendor/bin/security-checker security:check --end-point=http://security.sensiolabs.org/check_lock'

.PHONY: test lint static-analysis phpunit coding-standards composer-validate
test: build lint static-analysis coding-standards composer-validate stop ## Run all test suites
