BASE_DIRECTORY ?= $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

.PHONY: install
install:
	composer install

.PHONY: phpcs
phpcs:
	./vendor/bin/phpcs --standard=./vendor/spryker/code-sniffer/Spryker/ruleset.xml ./src/FondOfCodeception/* ./src/Propel/ ./tests/FondOfCodeception/* ./tests/Propel/

.PHONY: codeception
codeception:
	./vendor/bin/codecept run --coverage --coverage-xml --coverage-html

.PHONY: ci
ci: phpcs codeception
