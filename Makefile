.PHONY: *

install:
	composer install

check: cs quality test

quality:
	vendor/bin/phpstan --memory-limit=2G analyse

cs:
	vendor/bin/phpcs

cs-fix:
	vendor/bin/phpcbf

test:
	vendor/bin/phpunit
