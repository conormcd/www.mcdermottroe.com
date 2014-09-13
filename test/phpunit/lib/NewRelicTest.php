<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for NewRelic.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NewRelicTest
extends TestCase
{
    /**
     * Minimally exercise NewRelic::javaScriptHeader.
     *
     * @return void
     */
    public function testJavaScriptHeader() {
        // @codingStandardsIgnoreStart
        function newrelic_get_browser_timing_header() {
            return 'Dummy JavaScript';
        }
        // @codingStandardsIgnoreEnd
        $this->assertEquals('Dummy JavaScript', NewRelic::javaScriptHeader());
    }

    /**
     * Minimally exercise NewRelic::javaScriptFooter.
     *
     * @return void
     */
    public function testJavaScriptFooter() {
        // @codingStandardsIgnoreStart
        function newrelic_get_browser_timing_footer() {
            return 'Dummy JavaScript';
        }
        // @codingStandardsIgnoreEnd
        $this->assertEquals('Dummy JavaScript', NewRelic::javaScriptFooter());
    }

    /**
     * Minimally exercise NewRelic::transaction.
     *
     * @return void
     */
    public function testTransaction() {
        NewRelic::transaction(null, null);
    }
}

?>
