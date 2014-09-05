<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for all the controllers.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class ControllerTestCase
extends TestCase
{
    /**
     * Make sure that the get method returns some content.
     *
     * @return void
     */
    public function testGetReturnsSomeContent() {
        $controller = $this->sampleController();
        $res = $controller->get();
        $this->assertNotNull($res->body());
    }

    /**
     * Check that there's a Content-Length header and that it has a correct
     * value.
     *
     * @return void
     */
    public function testGetCreatesContentLengthHeader() {
        $controller = $this->sampleController();
        $res = $controller->get();

        $this->assertNotNull($res->body());
        $this->assertNotNull($res->headers());
        $this->assertTrue($res->headers()->exists('Content-Length'));
        $this->assertEquals(
            strlen($res->body()),
            $res->headers()->get('Content-Length')
        );
    }

    /**
     * Test that the constructor detects a bad \Klein\Request object.
     *
     * @return void
     */
    public function testControllerBadRequest() {
        $controller_name = $this->controllerName();
        $this->assertException(
            function () use ($controller_name) {
                new $controller_name(null, $this->res());
            }
        );
    }

    /**
     * Test that the constructor detects a bad \Klein\Response object.
     *
     * @return void
     */
    public function testControllerBadResponse() {
        $controller_name = $this->controllerName();
        $this->assertException(
            function () use ($controller_name) {
                new $controller_name($this->req(), null);
            }
        );
    }

    /**
     * Test the view method.
     *
     * @return void.
     */
    public function testView() {
        $controller = new TestController($this->req(), $this->res());
        $this->assertNotNull($controller->view());
        $this->assertEquals('test', $controller->view());
    }

    /**
     * Test the view method when there's an output format specified.
     *
     * @return void.
     */
    public function testViewWithOutputFormat() {
        $req = $this->req();
        $req->output_format = 'xml';
        $controller = new TestController($req, $this->res());
        $this->assertNotNull($controller->view());
        $this->assertEquals('test_xml', $controller->view());
    }

    /**
     * A wrapper for instantiating controllers in a useful form for testing.
     *
     * @param object $req The klein \Klein\Request object.
     * @param object $res The klein \Klein\Response object.
     *
     * @return object A controller instance.
     */
    protected function create($req = null, $res = null) {
        $controller = $this->controllerName();
        $req = $this->req($req);
        $res = $this->res($res);
        if (!$req->action) {
            $req->action = 'error';
        }
        return new $controller($req, $res);
    }

    /**
     * Get an instance of the controller under test on which it is safe to call
     * the get method in order to test the output of it.
     *
     * @return object The controller on which to run some of the tests.
     */
    protected function sampleController() {
        return $this->create();
    }

    /**
     * A wrapper for getting a \Klein\Request object without needing null and
     * type checking.
     *
     * @param object $req A \Klein\Request object or null.
     *
     * @return object A \Klein\Request object.
     */
    protected function req($req = null) {
        if ($req && !($req instanceof \Klein\Request)) {
            throw new Exception(
                var_export($req, true) . " is not an instance of \Klein\Request"
            );
        }
        return ($req ? $req : new \Klein\Request());
    }

    /**
     * A wrapper for getting a \Klein\Response object without needing null and
     * type checking.
     *
     * @param object $res A \Klein\Response object or null.
     *
     * @return object A \Klein\Response object.
     */
    protected function res($res = null) {
        if ($res && !($res instanceof \Klein\Response)) {
            throw new Exception(
                var_export($res, true) . " is not an instance of \Klein\Response"
            );
        }
        return ($res ? $res : new \Klein\Response());
    }

    /**
     * The name of the controller under test.
     *
     * @return string The name of the controller under test.
     */
    protected function controllerName() {
        return preg_replace('/Test$/', '', get_class($this));
    }
}

/**
 * A dummy controller for testing things that are otherwise hard to reach.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestController
extends Controller
{
    /**
     * Init.
     *
     * @param object $request  See parent class.
     * @param object $response See parent class.
     */
    public function __construct($request, $response) {
        $this->action = 'test';
        parent::__construct($request, $response);
        $this->output_format = $request->output_format;
    }
}

?>
