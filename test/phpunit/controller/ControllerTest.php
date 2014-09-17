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

        $controller = $this->create($req, $res);
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
                new $controller_name($this->req(), $this->res());
            }
        );
    }

    /**
     * Test setCacheHeaders.
     *
     * @return void
     */
    public function testSetCacheHeaders() {
        $req = $this->req();
        $res = $this->res();

        $model = new TestHeadersModel();
        $controller = new TestHeadersController($req, $res, $model);
        $controller->get();

        $this->assertEquals(
            'public max-age=0',
            $res->headers()->get('Cache-Control')
        );
        $this->assertRegexp(
            '/^\w{3}, \d\d \w{3} \d{4} \d\d:\d\d:\d\d GMT$/',
            $res->headers()->get('Expires')
        );
    }

    /**
     * Ensure that Content-Type and Last-Modified headers can be set from the
     * model.
     *
     * @return void
     */
    public function testSetHeaders() {
        $req = $this->req();
        $res = $this->res();

        $model = new TestHeadersModel();
        $controller = new TestHeadersController($req, $res, $model);
        $controller->get();

        $content_type = $res->headers()->get('Content-Type');
        $last_modified = $res->headers()->get('Last-Modified');

        $this->assertEquals($model->mimeType(), $content_type);
        $this->assertEquals($model->lastModified(), $last_modified);
    }
}

/**
 * Test controller for ControllerTest->testSetHeaders()
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestHeadersController
extends Controller
{
    /**
     * See Controller::__construct
     *
     * @param \Klein\Request  $request  The Klein request object.
     * @param \Klein\Response $response The Klein response object.
     * @param Model           $model    The model to use. (Should be an
     *                                  instance of TestHeadersModel.)
     */
    public function __construct($request, $response, $model) {
        $this->action = 'error';
        $this->model = $model;
        parent::__construct($request, $response);
    }

    /**
     * Deliberately break the cache control method to ensure that a minimum
     * value of max-age=0 is set.
     *
     * @return See Controller->cacheControl().
     */
    public function cacheControl() {
        $cache_control = parent::cacheControl();
        unset($cache_control['max-age']);
        return $cache_control;
    }
}

/**
 * Test model for ControllerTest->testSetHeaders()
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestHeadersModel
extends Model
{
    /**
     * A fixed dummy MIME type.
     *
     * @return string A dummy MIME type.
     */
    public function mimeType() {
        return 'dummy/mime+type';
    }

    /**
     * A Last-Modified header value.
     *
     * @return string A fixed, dummy Last-Modified header value.
     */
    public function lastModified() {
        return 'Sun, 07 Sep 2014 08:31:54 +0100';
    }

    /**
     * The value for the ETag.
     *
     * @return string An ETag for this model.
     */
    public function eTag() {
        return md5(__METHOD__);
    }
}

?>
