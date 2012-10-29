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

require_once __DIR__ . '/ControllerTestCase.php';

require_once dirname(dirname(dirname(__DIR__))) . '/controller/Controller.php';
require_once dirname(dirname(dirname(__DIR__))) . '/controller/ErrorController.php';

/**
 * Test the ErrorController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorControllerTest
extends ControllerTestCase
{
    /**
     * Create a basic ErrorController to do the common controller tests.
     *
     * @return object An instance of ErrorController.
     */
    public function sampleController() {
        return $this->create('ErrorController');
    }

    /**
     * Test an ErrorController with an exception.
     *
     * @return void
     */
    public function testWithException() {
        // Generate the exception to test.
        $exception = null;
        try {
            throw new Exception('Test exception', 503);
        } catch (Exception $e) {
            $exception = $e;
        }

        $controller = $this->create(
            function ($req, $res) use ($exception) {
                return new ErrorController($req, $res, $exception);
            }
        );
        $res = $this->trapOutput(
            function () use ($controller) {
                $controller->get();
            }
        );
        $headers = _Request::$_headers->headers;
        if (!$headers) {
            $headers = array();
        }
        $this->assertArrayHasKey('HTTP/1.0 503', $headers);
        $this->assertNotNull($res['output']);
        $this->assertNull($res['return']);
        $this->assertNull($res['exception']);
    }

    /**
     * Test an ErrorController with an exception which has as bad error code.
     *
     * @return void
     */
    public function testWithExceptionWithBadErrorCode() {
        // Generate the exception to test.
        $exception = null;
        try {
            throw new Exception('Test exception', 12345);
        } catch (Exception $e) {
            $exception = $e;
        }

        $controller = $this->create(
            function ($req, $res) use ($exception) {
                return new ErrorController($req, $res, $exception);
            }
        );
        $res = $this->trapOutput(
            function () use ($controller) {
                $controller->get();
            }
        );
        $headers = _Request::$_headers->headers;
        if (!$headers) {
            $headers = array();
        }
        $this->assertArrayHasKey('HTTP/1.0 500', $headers);
        $this->assertNotNull($res['output']);
        $this->assertNull($res['return']);
        $this->assertNull($res['exception']);
    }
}

?>
