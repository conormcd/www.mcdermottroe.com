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

require_once __DIR__ . '/HeaderCatcher.php';
require_once __DIR__ . '/TestCase.php';

require dirname(dirname(__DIR__)) . '/lib/autoloader.php';

/**
 * Test the routing.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class RoutingTest
extends TestCase
{
    /**
     * Test a known-good route to double-check {@link assertRoute}.
     *
     * @return void
     */
    public function testKnownGoodRoute() {
        $this->assertRoute('GET', '/blog');
    }

    /**
     * Test a known-bad route to double-check {@link assertRoute}.
     *
     * @return void
     */
    public function testKnownBadRoute() {
        $this->assertRoute('GET', '/does-not-exist', 404);
    }

    /**
     * Test a selection of routes derived from actual traffic.
     *
     * @return void
     */
    public function testSampleRoutes() {
        $root = dirname(dirname(__DIR__));
        $static_root = "$root/public";
        $sample_data = file_get_contents("$root/test/data/routes.txt");
        foreach (preg_split('/[\r\n]+/', $sample_data) as $line) {
            if (preg_match('/^([A-Z]+) (\S+)(?: (\d+))?/', $line, $matches)) {
                $method = $matches[1];
                $uri = $matches[2];
                $status = isset($matches[3]) ? $matches[3] : 200;
                if (!file_exists($static_root . $uri)) {
                    $this->assertRoute($method, $uri, $status);
                }
            }
        }
    }

    /**
     * Assert that a particular route results in a particular status.
     *
     * @param string $method          The HTTP method to use.
     * @param string $uri             The path portion of the URI to test.
     * @param int    $expected_status The HTTP status code which should be the 
     *                                result of the call to the specified 
     *                                route.
     *
     * @return void
     */
    private function assertRoute($method, $uri, $expected_status = 200) {
        global $__routes, $__namespace;
        $__routes = null;
        $__namespace = null;
        
        $root = dirname(dirname(__DIR__));
        include "$root/config/routes.php";
        
        autoloader($root);
        
        _Request::$_headers = _Response::$_headers = new HeaderCatcher();
        dispatch($uri, $method, null, true);
        $headers = _Response::$_headers->headers;
        if (!$headers) {
            $headers = array();
        }

        $header_found = ($expected_status == 200);
        foreach ($headers as $key => $value) {
            if (preg_match('#^HTTP/\d\.\d\s+\d\d\d(?:$|\D)#', $key)) {
                $this->assertNull($value);
                $status = preg_split('/\s+/', $key);
                $this->assertEquals(
                    $expected_status,
                    intval($status[1]),
                    "Bad route: $method $uri"
                );
                $header_found = true;
            }
        }
        $this->assertTrue($header_found);
    }
}

?>
