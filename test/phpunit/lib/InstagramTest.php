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
            JSON::encode(
                array(
                    'data' => array(
                        array(
                            'created_time' => 1388534400,
                            'link' => 'http://fake.link',
                            'images' => array(
                                'standard_resolution' => array(
                                    'url' => 'http://fake.image.url',
                                )
                            ),
                            'caption' => array(
                                'text' => 'Fake Instagram caption',
                            )
                        )
                    )
                )
            )
        );

        $instagram = $this->getTestInstance();
        $images = $instagram->getStream();

        $this->assertNotNull($images);
        $this->assertTrue(count($images) == 1);
        $this->assertEquals(
            array(
                'timestamp' => 1388534400,
                'link' => 'http://fake.link',
                'image' => 'http://fake.image.url',
                'caption' => 'Fake Instagram caption',
                'title' => '1st January 2014',
                'isInstagramPhoto' => true,
            ),
            $images[0]
        );
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
        $this->assertTrue(count($images) == 0);
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
