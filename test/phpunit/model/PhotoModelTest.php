<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the PhotoModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotoModelTest
extends ModelTestCase
{
    /**
     * Test PhotoModel#photoID
     *
     * @return void
     */
    public function testPhotoId() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->photoID());
        }
    }

    /**
     * Test PhotoModel#title
     *
     * @return void
     */
    public function testTitle() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->title());
        }
    }

    /**
     * Test PhotoModel#slug
     *
     * @return void
     */
    public function testSlug() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->slug());
        }
    }

    /**
     * Test PhotoModel#fullsize
     *
     * @return void
     */
    public function testFullsize() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->fullsize());
        }
    }

    /**
     * Test PhotoModel#large
     *
     * @return void
     */
    public function testLarge() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->large());
        }
    }

    /**
     * Test PhotoModel#thumbnail
     *
     * @return void
     */
    public function testThumbnail() {
        foreach ($this->samplePhotos() as $photo) {
            $this->assertNotNull($photo->thumbnail());
        }
    }

    /**
     * Get a test copy of PhotoModel.
     *
     * @return PhotoModel An instance of PhotoModel which can be tested.
     */
    protected function createTestObject() {
        $photos = $this->samplePhotos();
        return $photos[0];
    }

    /**
     * Generate some test photo objects.
     *
     * @return array An array of PhotoModel objects.
     */
    private function samplePhotos() {
        $provider = PhotoProvider::getInstance();
        $photos = array();
        foreach ($provider->getAlbums() as $album) {
            foreach ($provider->getPhotos($album) as $photo) {
                $photos[] = $photo;
            }
        }
        return $photos;
    }
}

?>
