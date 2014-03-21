<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the StaticFileController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class StaticFileControllerTest
extends ControllerTestCase
{
    /**
     * Create a basic StaticFileController to do the common controller tests.
     *
     * @param string $file The path to the file to serve, relative to the
     *                     public dir.
     *
     * @return object An instance of StaticFileController.
     */
    public function sampleController($file = null) {
        $file = $file ?: '/css/default.css';
        $req = new \Klein\Request(
            array(),
            array(),
            array(),
            array('REQUEST_URI' => $file)
        );
        return $this->create(null, $req);
    }

    /**
     * Test with a file that does not exist.
     *
     * @return void
     */
    public function testFileNotFound() {
        $controller = $this->sampleController('/a/file/that/is/not/here');
        $exception = $this->assertException(
            function () use ($controller) {
                return $controller->get();
            }
        );
        $this->assertEquals(404, $exception->getCode());
    }
}

?>
