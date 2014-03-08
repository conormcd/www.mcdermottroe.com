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
     * A sample instance of the controller under test
     *
     * @return object An instance of PhotosController.
     */
    protected function sampleController() {
        return $this->create('PhotosController');
    }

    /**
     * Make sure the controller works when you pass it an album.
     *
     * @return void
     */
    public function testWithAlbumSpecified() {
        $controller = $this->create(
            function ($klein, $req, $res) {
                $req->album = 'ISSFWorldCupGranadaJul2013';
                return new PhotosController($klein, $req, $res);
            }
        );
        $result = $this->runController($controller);

        $this->assertEquals(200, $result['status']);
        $this->assertNotNull($result['output']);
    }
}

?>
