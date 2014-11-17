<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the PhotosModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotosModelTest
extends PageableModelTestCase
{
    /**
     * Proxy for the PhotosModel constructor.
     *
     * @param int $page     The page number to fetch.
     * @param int $per_page The size of the page.
     *
     * @return object An instance of PhotosModel.
     */
    public function createTestObject($page = null, $per_page = null) {
        if ($page === null) {
            $page = 1;
        }
        if ($per_page === null) {
            $per_page = PhotosModel::PHOTOS_PER_PAGE;
        }
        $start = (($page - 1) * $per_page) + 1;
        $album = FakeFlickr::albumSlugForTesting();
        return new PhotosModel($album, $start, $per_page);
    }

    /**
     * Try out the model in album view.
     *
     * @return void
     */
    public function testAlbumView() {
        $model = new PhotosModel();
        $this->assertEquals($model->albums(), $model->all());
        $this->assertEquals($model->albums(), $model->page());
    }

    /**
     * Check the link is fiddled to work with the photo URL style.
     *
     * @return void
     */
    public function testLink() {
        $photos = $this->createTestObject();
        $album = FakeFlickr::albumSlugForTesting();
        $this->assertEquals("/photos/$album", $photos->link());
    }

    /**
     * Test the title with a default page.
     *
     * @return void
     */
    public function testTitle() {
        $photos = new PhotosModel();
        $this->assertEquals('Photos', $photos->title());
    }

    /**
     * Test the title when we have an album.
     *
     * @return void
     */
    public function testTitleAlbum() {
        $photos = $this->createTestObject();
        $this->assertEquals('A fake photo album', $photos->title());
    }

    /**
     * Test the title when we have an album and are showing one per page.
     *
     * @return void
     */
    public function testTitleOnePerPage() {
        $photos = $this->createTestObject(1, 1);
        $this->assertEquals(
            'Photo number 0 in the set.',
            $photos->title()
        );
    }

    /**
     * Check the TTL of a default page.
     *
     * @return void
     */
    public function testTtl() {
        $photos = new PhotosModel();
        $this->assertGreaterThan(0, $photos->ttl());
    }

    /**
     * Check the TTL when we have an album.
     *
     * @return void
     */
    public function testTtlAlbum() {
        $photos = $this->createTestObject();
        $this->assertGreaterThan(0, $photos->ttl());
    }

    /**
     * Check the TTL when we have an album and are showing one per page.
     *
     * @return void
     */
    public function testTtlOnePerPage() {
        $photos = $this->createTestObject(1, 1);
        $this->assertGreaterThan(0, $photos->ttl());
    }
}

?>
