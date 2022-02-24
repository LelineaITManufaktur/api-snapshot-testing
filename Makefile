PHP ?= php

.PHONY: install-ci
install-ci:
	$(PHP) $(shell which composer) install -n

.PHONY: test
test: test-unit test-phpstan test-phpcsfixer test-phpcodesniffer

.PHONY: test-unit
test-unit:
	 $(PHP) vendor/bin/phpunit --testsuite unit

.PHONY: test-phpstan
test-phpstan:
	$(PHP) vendor/bin/phpstan analyse --memory-limit=-1 -c phpstan.neon

.PHONY: test-phpcsfixer
test-phpcsfixer:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff -v

.PHONY: phpcsfixer
phpcsfixer:
	$(PHP) vendor/bin/php-cs-fixer fix --diff -v

.PHONY: test-phpcodesniffer
test-phpcodesniffer:
	$(PHP) vendor/bin/phpcs

.PHONY: test-phpcodesniffer-fixer
test-phpcodesniffer-fixer:
	$(PHP) vendor/bin/phpcbf

.PHONY: format
format: test-phpcodesniffer-fixer phpcsfixer

SYMFONY_ENV ?= test

.PHONY: set-symfony-env-dev
set-symfony-env-dev:
	$(eval SYMFONY_ENV:=dev)
