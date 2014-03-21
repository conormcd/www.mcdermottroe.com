<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Test Sentry.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class SentryTest
extends FactoryTestCase
{
    /**
     * Basic setup.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->class = get_class();
        $_ENV['SENTRY_DSN'] = 'https://abcd:ef90@app.getsentry.com/1234';
    }
}

?>
