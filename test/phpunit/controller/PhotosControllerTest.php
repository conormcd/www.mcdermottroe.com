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
        $controller = $this->create($req);
        $res = $controller->get();

        $this->assertEquals(200, $res->status()->getCode());
        $this->assertNotNull($res->body());
    }

    /**
     * Make sure the controller works when you pass it an album.
     *
     * @return void
     */
    public function testWithOnlyOnePhoto() {
        $req = $this->req();
        $req->album = FakeFlickr::albumSlugForTesting();
        $req->perpage = 1;
        $req->start = 1;
        $controller = $this->create($req);
        $res = $controller->get();

        $this->assertEquals(200, $res->status()->getCode());
        $this->assertNotNull($res->body());
    }
}

?>
