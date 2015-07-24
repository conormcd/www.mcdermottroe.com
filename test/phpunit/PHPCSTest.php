<?php

/**
 * Run PHP_CodeSniffer over the source.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PHPCSTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Run PHP_CodeSniffer over the source.
     *
     * @return void
     */
    public function testPHPCS() {
        $cli = new PHP_CodeSniffer_CLI();
        $cli->setCommandLineValues(
            array(
                '--standard=test/phpcs.xml',
                '--extensions=php',
                '--ignore=vendor',
                '--no-colors',
                '--report-full',
                '.'
            )
        );
        ob_start();
        $failures = $cli->process();
        $output = ob_get_clean();
        $this->assertEquals(0, $failures, $output);
    }
}

?>
