<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the StaticFileModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class StaticFileModelTest
extends ModelTestCase
{
    /**
     * Test that the Last Modified header will be set and appropriately
     * formatted.
     *
     * @return void
     */
    public function testLastModified() {
        $instance = $this->createTestObject();
        $this->assertNotNull($instance->lastModified());
        $this->assertRegexp(
            '/^\w+, \d+ \w+ \d\d\d\d \d\d:\d\d:\d\d GMT$/',
            $instance->lastModified()
        );
    }

    /**
     * Test that CSS URLs get served as text/css.
     *
     * @return void
     */
    public function testMimeTypeCSS() {
        $instance = $this->createTestObject();
        $this->assertNotNull($instance->mimeType());
        $this->assertEquals('text/css', $instance->mimeType());
    }

    /**
     * Test that PNG URLs get served as image/png.
     *
     * @return void
     */
    public function testMimeTypePNG() {
        $instance = $this->createTestObject('/boards/icons/boards.png');
        $this->assertNotNull($instance->mimeType());
        $this->assertEquals('image/png', $instance->mimeType());
    }

    /**
     * Test that the path method correctly calculates the path of a file in the
     * public directory.
     *
     * @return void
     */
    public function testPath() {
        $uri = '/css/default.css';
        $path = dirname(dirname(dirname(__DIR__))) . '/public' . $uri;

        $instance = $this->createTestObject();
        $this->assertNotNull($instance->path());
        $this->assertEquals($path, $instance->path());
    }

    /**
     * Ensure that calling path() on a directory throws an exception.
     *
     * @return void
     */
    public function testPathWithDir() {
        $instance = $this->createTestObject('/css/');
        $this->assertException(
            function () use ($instance) {
                $instance->path();
            }
        );
    }

    /**
     * Get a copy of StaticFileModel for testing.
     *
     * @param string $uri The URI for the file.
     *
     * @return StaticFileModel An instance which can be tested.
     */
    protected function createTestObject($uri = '/css/default.css') {
        $obj = new StaticFileModel();
        $obj->uri($uri);
        return $obj;
    }
}

?>
