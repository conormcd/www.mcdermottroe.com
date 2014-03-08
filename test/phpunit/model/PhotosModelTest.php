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
        $album = 'IsleOfManEasterShootApr2009';
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
}

?>
