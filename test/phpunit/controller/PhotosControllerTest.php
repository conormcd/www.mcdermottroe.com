<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the PhotosController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotosControllerTest
extends ControllerTestCase
{
    /**
     * Make sure the controller works when you pass it an album.
     *
     * @return void
     */
    public function testWithAlbumSpecified() {
        $req = $this->req();
        $req->album = FakeFlickr::albumSlugForTesting();
        $controller = $this->create(null, $req);
        $res = $controller->get();

        $this->assertEquals(200, $res->status()->getCode());
        $this->assertNotNull($res->body());
    }
}

?>
