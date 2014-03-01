<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(dirname(__DIR__)) . '/config/environment.php';

/**
 * Generic test case for adding more assertions.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class TestCase
extends PHPUnit_Framework_TestCase
{
    /**
     * Common set up functionality for all tests.
     *
     * @return void
     */
    public function setUp() {
        date_default_timezone_set('Europe/Dublin');

        // Dummy Amazon Affiliate data
        $_ENV['AMAZON_AFFILIATE_TAG'] = 'affiliate_tag';
        $_ENV['AMAZON_AFFILIATE_LINK_CODE'] = 'affiliate_link_code';
        $_ENV['AMAZON_AFFILIATE_CAMP'] = 1234566789;
        $_ENV['AMAZON_AFFILIATE_CREATIVE'] = 26667;

        // Get rid of the SENTRY_DSN so that test failures don't end up in
        // Sentry.
        $_ENV['SENTRY_DSN'] = null;
    }

    /**
     * Assert that an exception is thrown by a given chunk of code.
     *
     * @param callable $func    The function which is expected to throw an
     *                          exception.
     * @param array    $args    The arguments to pass to the function.
     * @param string   $message The message to show if the assertion fails.
     *
     * @return exception        The exception which was thrown.
     */
    protected function assertException($func, $args = null, $message = null) {
        $this->assertTrue(
            is_callable($func),
            "Exception block was not callable."
        );
        if ($args === null) {
            $args = array();
        }
        if ($message === null) {
            $function_name = '<anonymous function>';
            if (is_array($func)) {
                if (is_object($func[0])) {
                    $function_name = get_class($func[0]) . '->';
                } else {
                    $function_name = "{$func[0]}::";
                }
                $function_name .= $func[1];
            } else if (is_string($func)) {
                $function_name = $func;
            }
            $message = sprintf(
                'Expected %s(%s) to throw an exception but it did not.',
                $function_name,
                $args ? var_export($args, true) : ''
            );
        }

        $exception = null;
        try {
            call_user_func_array($func, $args);
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, $message);
        return $exception;
    }
}

?>
