<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the Controller class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ControllerTest
extends ControllerTestCase
{
    /**
     * Test Controller as a controller in its own right, rather than as a base
     * implementation for a more specific controller.
     *
     * @return void
     */
    public function testConstructorAsActiveController() {
        $req = $this->req();
        $res = $this->res();
        $req->action = 'about';

        $controller = $this->create(null, $req, $res);
        $res = $controller->get();

        $this->assertNotNull($res->body());
        $this->assertRegexp('/<html>/', $res->body());
    }

    /**
     * Test that the constructor detects when no action has been provided.
     *
     * @return void
     */
    public function testControllerNoAction() {
        $controller_name = $this->controllerName();
        $this->assertException(
            function () use ($controller_name) {
                new $controller_name($this->klein(), $this->req(), $this->res());
            }
        );
    }
}

?>
