<?php

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

        // Mark the environment as loaded.
        Environment::load();

        // Now kill everything in the environment since we need to not assume
        // any particular environment variables.
        foreach (array_keys($_ENV) as $var) {
            unset($_ENV[$var]);
        }

        // Dummy Amazon Affiliate data
        $_ENV['AMAZON_AFFILIATE_TAG'] = 'affiliate_tag';
        $_ENV['AMAZON_AFFILIATE_LINK_CODE'] = 'affiliate_link_code';
        $_ENV['AMAZON_AFFILIATE_CAMP'] = 1234566789;
        $_ENV['AMAZON_AFFILIATE_CREATIVE'] = 26667;

        // Disable all caching
        $_ENV['CACHE_ENABLE'] = false;

        // Dummy Flickr credentials
        $_ENV['FLICKR_API_KEY'] = md5(rand());
        $_ENV['FLICKR_API_SECRET'] = md5(rand());
        $_ENV['FLICKR_API_USER'] = md5(rand());

        // Dummy Instagram credentials.
        $_ENV['INSTAGRAM_CLIENT_ID'] = 'fake fake fake';
        $_ENV['INSTAGRAM_CLIENT_SECRET'] = 'fake fake fake';
        $_ENV['INSTAGRAM_USER_ID'] = 123456;

        // Use fakes for the external service providers.
        $_ENV['EXCEPTION_TRACKER'] = 'FakeSentry';
        $_ENV['HTTP_CLIENT_CLASS'] = 'FakeHTTPClient';
        $_ENV['INSTAGRAM_CLASS'] = 'FakeInstagram';
        $_ENV['PHOTO_PROVIDER'] = 'FakeFlickr';
    }

    /**
     * Common teardown for all tests.
     *
     * @return void
     */
    public function tearDown() {
        FakeHTTPClient::reset();
        Cache::clear();
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
