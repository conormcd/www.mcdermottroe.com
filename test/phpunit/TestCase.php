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

        // Kill everything in the environment since we need to not assume
        // any particular environment variables.
        foreach (array_keys($_ENV) as $var) {
            unset($_ENV[$var]);
        }

        // Mark the environment as loaded.
        $_ENV['ENVIRONMENT_CANARY'] = 'set';

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

        // Dummy GitHub info
        $_ENV['GITHUB_USER'] = 'fake_github_user';

        // Use fakes for the external service providers.
        $_ENV['EXCEPTION_TRACKER'] = 'FakeSentry';
        $_ENV['GITHUB_CLASS'] = 'FakeGitHub';
        $_ENV['HTTP_CLIENT_CLASS'] = 'FakeHTTPClient';
        $_ENV['PHOTO_PROVIDER'] = 'FakeFlickr';
    }

    /**
     * Common teardown for all tests.
     *
     * @return void
     */
    public function tearDown() {
        FakeHTTPClient::reset();
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

    /**
     * Find out the version and implementation of PHP we're running on.
     *
     * @return array The version of the PHP language, the implementation and
     *               the version of the implementation. All versions are
     *               integers returned by comparableVersion.
     */
    protected function phpVersion() {
        $version = array();
        if (defined('HHVM_VERSION')) {
            $version['version'] = preg_replace('/-hhvm$/', '', PHP_VERSION);
            $version['implementation'] = 'hhvm';
            $version['implementation_version'] = HHVM_VERSION;
        } else {
            $version['version'] = PHP_VERSION;
            $version['implementation'] = 'php';
            $version['implementation_version'] = PHP_VERSION;
        }
        $version['version'] = $this->comparableVersion($version['version']);
        $version['implementation_version'] = $this->comparableVersion(
            $version['implementation_version']
        );
        return $version;
    }

    /**
     * Some tests need to be skipped on older versions of HHVM so that they
     * don't trip over known bugs in that implementation. This method can be
     * used to check if we're running on HHVM and if the version of HHVM is
     * older than a certain value.
     *
     * @param string $version A version of HHVM to compare against.
     *
     * @return boolean True iff the current implementation of PHP is HHVM *and*
     *                 if the version of HHVM is strictly less than $version.
     */
    protected function isHHVMAndOlderThan($version) {
        $php = $this->phpVersion();
        return  $php['implementation'] == 'hhvm' &&
                $php['implementation_version'] < $this->comparableVersion(
                    $version
                );
    }

    /**
     * Transform an x.y.z version into an integer that can be compared with
     * others.
     *
     * @param string $version_string A version string.
     *
     * @return int An integer representation of the version string.
     */
    private function comparableVersion($version_string) {
        if (!preg_match('/^\d+(?:\.\d+){0,2}$/', $version_string)) {
            throw new Exception("Bad version: $version_string");
        }
        $version_parts = explode('.', $version_string);
        $version = $version_parts[0] * 1000 * 1000;
        if (count($version_parts) > 1) {
            $version += $version_parts[1] * 1000;
            if (count($version_parts) > 2) {
                $version += $version_parts[2];
            }
        }
        return $version;
    }
}

?>
