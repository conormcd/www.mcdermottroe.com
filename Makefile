test: phpcpd phpcs phploc phpmd phpunit

doc: doc/html/index.xhtml

clean:
	rm -rf doc/html
	rm -rf doc/phpunit-coverage
	rm -rf doc/xml
	rm -f doc/phpcpd.xml
	rm -f doc/phpcs.xml
	rm -f doc/phploc.xml
	rm -f doc/phpmd.xml

phpcpd: doc/phpcpd.xml
phpcs: doc/phpcs.xml
phploc: doc/phploc.xml
phpmd: doc/phpmd.xml
phpunit: doc/phpunit-coverage
vendor/bin/phpunit: vendor
vendor/bin/phpcpd: vendor
vendor/bin/phpdox: vendor
vendor/bin/phpcs: vendor
vendor/bin/phpmd: vendor

vendor:
	composer install

doc/html/index.xhtml: doc/phpcs.xml doc/phploc.xml doc/phpmd.xml vendor/bin/phpdox
	vendor/bin/phpdox --file doc/phpdox.xml

doc/phpcpd.xml: vendor/bin/phpcpd
	vendor/bin/phpcpd --log-pmd doc/phpcpd.xml --exclude vendor .

doc/phpcs.xml: vendor/bin/phpcs
	vendor/bin/phpcs \
		--standard=test/phpcs.xml \
		--extensions=php \
		--ignore=vendor \
		--report-full \
		--report-xml=doc/phpcs.xml \
		.

doc/phploc.xml: vendor/bin/phploc
	vendor/bin/phploc --exclude vendor --log-xml doc/phploc.xml .

doc/phpmd.xml: test/phpmd.xml vendor/bin/phpmd
	vendor/bin/phpmd . xml test/phpmd.xml --exclude vendor > doc/phpmd.xml

doc/phpunit-coverage: vendor/bin/phpunit
	vendor/bin/phpunit --coverage-xml doc/phpunit-coverage --verbose --stderr test/phpunit
