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
        $result = $this->runController($this->sampleController());
        $this->assertNotNull($result['output']);
    }

    /**
     * Check that there's a Content-Length header and that it has a correct
     * value.
     *
     * @return void
     */
    public function testGetCreatesContentLengthHeader() {
        $result = $this->runController($this->sampleController());

        $this->assertNotNull($result['output']);
        $this->assertArrayHasKey('content-length', $result['headers']);
        $this->assertEquals(
            strlen($result['output']),
            $result['headers']['content-length']
        );
    }

    /**
     * Make sure that any overrides/extensions of the constructor don't break
     * the checking of the constructor parameters.
     *
     * @return void
     */
    public function testConstructorValidatesParameters() {
        $req = new \Klein\Request();
        $req->action = 'error';

        // Both not null
        $exception_thrown = false;
        try {
            new Controller(new \Klein\Klein(), $req, new \Klein\Response());
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertFalse(
            $exception_thrown,
            "Threw an exception even when request and response OK."
        );

        // Request null
        $exception_thrown = false;
        try {
            new Controller(null, new \Klein\Response());
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertTrue(
            $exception_thrown,
            "Failed to detect bad request object."
        );

        // Response null
        $exception_thrown = false;
        try {
            new Controller($req, null);
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertTrue(
            $exception_thrown,
            "Failed to detect bad response object."
        );

        // Both null
        $exception_thrown = false;
        try {
            new Controller(null, null);
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertTrue(
            $exception_thrown,
            "Failed to detect bad request/response object."
        );

        // Request with no action
        unset($req->action);
        $exception_thrown = false;
        try {
            new Controller(new \Klein\Request(), new \Klein\Response());
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertTrue(
            $exception_thrown,
            "Failed to detect bad request with no action."
        );
    }

    /**
     * A wrapper for instantiating controllers in a useful form for testing.
     *
     * @param mixed  $controller Either a string specifying the controller class to
     *                           create or a callable which takes two objects -
     *                           the request and response objects - and returns
     *                           an instance of the controller under test.
     * @param object $req        The klein \Klein\Request object.
     * @param object $res        The klein \Klein\Response object.
     * @param object $klein      The klein \Klein\Klein object.
     *
     * @return object A controller instance.
     */
    protected function create($controller, $req = null, $res = null, $klein = null) {
        $req = $this->req($req);
        $res = $this->res($res);
        $klein = $this->klein($klein);
        if (!$req->action) {
            $req->action = 'error';
        }
        if (is_string($controller)) {
            return new $controller($klein, $req, $res);
        } else {
            return call_user_func_array($controller, array($klein, $req, $res));
        }
    }

    /**
     * Get an instance of the controller under test on which it is safe to call
     * the get method in order to test the output of it.
     *
     * @return object The controller on which to run some of the tests.
     */
    protected abstract function sampleController();

    /**
     * Run a controller and capture the results.
     *
     * @param object $controller The controller to run.
     * @param object $method     The HTTP method to send to it.
     *
     * @return array An associative array with four members: 'output' which is
     *               a string with the body of the HTTP response, 'status'
     *               which is the HTTP status code returned, 'headers' which is
     *               an associative array containing all of the headers output
     *               by the controller and 'response' which is the
     *               \Klein\Response object from the controller.
     */
    protected function runController($controller, $method = 'get') {
        ob_start();
        $response = call_user_func(array($controller, $method));
        $output = ob_get_contents();
        ob_end_clean();
        return array(
            'output' => $output,
            'status' => $response->status()->getCode(),
            'headers' => $response->headers()->all(),
            'response' => $response,
        );
    }

    /**
     * A wrapper for getting a \Klein\Klein object without needing null and
     * type checking.
     *
     * @param object $klein A \Klein\Klein object or null.
     *
     * @return object A \Klein\Klein object.
     */
    private function klein($klein) {
        if ($klein && !($klein instanceof \Klein\Klein)) {
            throw new Exception(
                var_export($klein, true) . " is not an instance of \Klein\Klein"
            );
        }
        return ($klein ? $klein : new \Klein\Klein());
    }

    /**
     * A wrapper for getting a \Klein\Request object without needing null and
     * type checking.
     *
     * @param object $req A \Klein\Request object or null.
     *
     * @return object A \Klein\Request object.
     */
    private function req($req) {
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
    private function res($res) {
        if ($res && !($res instanceof \Klein\Response)) {
            throw new Exception(
                var_export($res, true) . " is not an instance of \Klein\Response"
            );
        }
        return ($res ? $res : new \Klein\Response());
    }
}

?>
