<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Tests for Instagram.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class InstagramTest
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
    }

    /**
     * Test {@link Instagram::getStream()}.
     *
     * @return void
     */
    public function testGetStream() {
        // Fake response for this call.
        FakeHTTPClient::addResponse(
            "#^https://api.instagram.com/.*/media/recent.*#",
            file_get_contents(
                dirname(dirname(__DIR__)) .
                '/data/instagram_users_media_recent.json'
            )
        );

        $instagram = $this->getTestInstance();
        $images = $instagram->getStream();

        $this->assertNotNull($images);
        $this->assertEquals(20, count($images));
    }

    /**
     * Test {@link Instagram::getStream()} with a 404.
     *
     * @return void
     */
    public function testGetStream404() {
        // Fake response for this call.
        FakeHTTPClient::addResponse(
            "#^https://api.instagram.com/.*/media/recent.*#",
            function () {
                return array(
                    'status' => 404,
                    'body' => '{}',
                );
            }
        );

        $instagram = $this->getTestInstance();
        $images = $instagram->getStream();

        $this->assertNotNull($images);
        $this->assertEquals(0, count($images));
        $this->assertNotNull(ExceptionTracker::getInstance()->lastException);
    }

    /**
     * Test Instagram#getInstance() with no environment variable for the class
     * name.
     *
     * @return void
     */
    public function testGetInstanceNoEnv() {
        unset($_ENV['INSTAGRAM_CLASS']);
        $instance = Instagram::getInstance();
        $this->assertNotNull($instance);
        $this->assertInstanceOf('Instagram', $instance);
    }

    /**
     * Generate a test Instagram object. We can't use Instagram::getInstance
     * since that's faked out so that other tests may run.
     *
     * @return Instagram An instance of the Instagram class.
     */
    private function getTestInstance() {
        return new Instagram(
            Environment::get('INSTAGRAM_CLIENT_ID'),
            Environment::get('INSTAGRAM_CLIENT_SECRET'),
            Environment::get('INSTAGRAM_USER_ID')
        );
    }
}

?>
