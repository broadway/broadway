.DEFAULT_GOAL:=help

.PHONY: dependencies
dependencies:
	composer install --no-interaction --no-suggest --no-scripts --ansi

.PHONY: test
test:
	vendor/bin/phpunit --testdox --exclude-group=none --colors=always

.PHONY: qa
qa: php-cs-fixer

.PHONY: php-cs-fixer
php-cs-fixer:
	vendor/bin/php-cs-fixer fix --no-interaction --allow-risky=yes --diff --verbose

.PHONY: changelog
changelog:
	git log $$(git describe --abbrev=0 --tags)...HEAD --no-merges --pretty=format:"* [%h](http://github.com/${TRAVIS_REPO_SLUG}/commit/%H) %s (%cN)"

# Based on https://suva.sh/posts/well-documented-makefiles/
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
