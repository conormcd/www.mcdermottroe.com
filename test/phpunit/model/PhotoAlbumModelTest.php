<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the PhotoAlbumModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotoAlbumModelTest
extends ModelTestCase
{
    /**
     * Test PhotoAlbumModel#albumID()
     *
     * @return void
     */
    public function testAlbumID() {
        $this->assertEquals(1234, $this->createTestObject()->albumID());
    }

    /**
     * Test PhotoAlbumModel#isPhotoAlbum()
     *
     * @return void
     */
    public function testIsPhotoAlbum() {
        $this->assertTrue($this->createTestObject()->isPhotoAlbum());
    }

    /**
     * Test title()
     *
     * @return void
     */
    public function testTitle() {
        $this->assertEquals(
            'A fake photo album',
            $this->createTestObject()->title()
        );
    }

    /**
     * Test timestamp()
     *
     * @return void
     */
    public function testTimestamp() {
        $this->assertGreaterThanOrEqual(
            time() - (7 * 86400),
            $this->createTestObject()->timestamp()
        );
    }

    /**
     * Test timestamp() where there's a timestamp hint in the album title.
     *
     * @return void
     */
    public function testTimestampFromTitle() {
        $instance = $this->createTestObject();
        $instance = new PhotoAlbumModel(
            PhotoProvider::getInstance(),
            $instance->albumID(),
            "My Holiday Photos - Jun 2008",
            strtotime('2013-01-01'),
            $instance->thumbnail()->photoID()
        );
        $this->assertEquals(
            strtotime('2008-06-01'),
            $instance->timestamp()
        );
    }

    /**
     * Test photos().
     *
     * @return void
     */
    public function testPhotos() {
        $photos = $this->createTestObject()->photos();
        $this->assertNotNull($photos);
        $this->assertTrue(count($photos) > 0);
        foreach ($photos as $photo) {
            $this->assertInstanceOf('PhotoModel', $photo);
        }
    }

    /**
     * Test slug()
     *
     * @return void
     */
    public function testSlug() {
        $this->assertEquals(
            'AFakePhotoAlbum',
            $this->createTestObject()->slug()
        );
    }

    /**
     * Test thumbnail()
     *
     * @return void
     */
    public function testThumbnail() {
        $thumbnail = $this->createTestObject()->thumbnail();
        $this->assertNotNull($thumbnail);
        $this->assertInstanceOf('PhotoModel', $thumbnail);
    }

    /**
     * Test thumbnail() when the thumbnail ID provided is not in the set.
     *
     * @return void.
     */
    public function testThumbnailWithBadThumbnailID() {
        $instance = $this->createTestObject();
        $instance = new PhotoAlbumModel(
            PhotoProvider::getInstance(),
            $instance->albumID(),
            $instance->title(),
            $instance->timestamp(),
            26667
        );

        $thumbnail = $instance->thumbnail();
        $this->assertNotNull($thumbnail);
        $this->assertInstanceOf('PhotoModel', $thumbnail);
        $this->assertNotEquals(26667, $thumbnail->photoID());
    }

    /**
     * Create a sample object for testing.
     *
     * @return PhotoAlbumModel An instance that can be tested.
     */
    protected function createTestObject() {
        $provider = PhotoProvider::getInstance();
        return $provider->albumForTesting();
    }
}

?>
