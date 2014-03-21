<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Test HTTPClient.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class HTTPClientTest
extends FactoryTestCase
{
    /**
     * Make sure we're testing the real, not the fake.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->class = get_class();
        $_ENV['HTTP_CLIENT_CLASS'] = 'HTTPClient';
    }

    /**
     * Test getInstance with no environment variable selecting a client class.
     *
     * @return void
     */
    public function testGetInstanceNoEnv() {
        unset($_ENV['HTTP_CLIENT_CLASS']);
        $client = HTTPClient::getInstance();
        $this->assertNotNull($client);
        $this->assertInstanceOf('HTTPClient', $client);
    }

    /**
     * Test that you can make a HTTP request.
     *
     * @return void
     */
    public function testGet() {
        $client = HTTPClient::getInstance();
        $output = $client->get('http://www.mcdermottroe.com/');
        $this->assertRegexp('/<html/', $output);
    }

    /**
     * Test that you can make a HTTP request which results in a 404.
     *
     * @return void
     */
    public function testGet404() {
        $client = HTTPClient::getInstance();
        $this->assertException(
            function () use ($client) {
                $client->get('http://www.mcdermottroe.com/does/not/exist');
            }
        );
    }
}

?>
