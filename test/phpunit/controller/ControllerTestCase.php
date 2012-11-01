<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

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

        $res = $this->trapOutput(
            function () use ($controller) {
                $controller->get();
            }
        );

        $this->assertNotNull($res['output']);
        $this->assertNull($res['return']);
        $this->assertNull($res['exception']);
    }

    /**
     * Check that there's a Content-Length header and that it has a correct
     * value.
     *
     * @return void
     */
    public function testGetCreatesContentLengthHeader() {
        $controller = $this->sampleController();

        $res = $this->trapOutput(
            function () use ($controller) {
                $controller->get();
            }
        );
        $headers = _Request::$_headers->headers;
        if (!$headers) {
            $headers = array();
        }

        $this->assertNotNull($res['output']);
        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertEquals(strlen($res['output']), $headers['Content-Length']);
    }

    /**
     * Make sure that any overrides/extensions of the constructor don't break
     * the checking of the constructor parameters.
     *
     * @return void
     */
    public function testConstructorValidatesParameters() {
        $req = new _Request();
        $req->action = 'error';

        // Both not null
        $exception_thrown = false;
        try {
            new Controller($req, new _Response());
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
            new Controller(null, new _Response());
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
            new Controller(new _Request(), new _Response());
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
     * @param object $req        The klein _Request object.
     * @param object $res        The klein _Response object.
     *
     * @return object A controller instance.
     */
    protected function create($controller, $req = null, $res = null) {
        $request = $req !== null ? $req : new _Request();
        $response = $res !== null ? $res : new _Response();
        if (!$request->action) {
            $request->action = 'error';
        }
        _Request::$_headers = _Response::$_headers = new HeaderCatcher();
        if (is_string($controller)) {
            return new $controller($request, $response);
        } else {
            return call_user_func_array($controller, array($request, $response));
        }
    }

    /**
     * Get an instance of the controller under test on which it is safe to call
     * the get method in order to test the output of it.
     *
     * @return object The controller on which to run some of the tests.
     */
    protected abstract function sampleController();
}

?>
