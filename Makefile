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

travis-env:
	ruby -e 'require "json"; env = ENV.select{|k,v| ["AMAZON_AFFILIATE_TAG", "AMAZON_AFFILIATE_LINK_CODE", "AMAZON_AFFILIATE_CAMP", "AMAZON_AFFILIATE_CREATIVE", "CACHE_ENABLE", "EXCEPTION_TRACKER", "FLICKR_API_KEY", "FLICKR_API_SECRET", "FLICKR_API_USER", "GITHUB_USER", "INSTAGRAM_CLIENT_ID", "INSTAGRAM_CLIENT_SECRET", "INSTAGRAM_USER_ID", "PHOTO_PROVIDER", "SENTRY_DSN"].include? k}; File.write("www.mcdermottroe.com.env", JSON.dump(env))'
	sudo mv www.mcdermottroe.com.env /etc/
