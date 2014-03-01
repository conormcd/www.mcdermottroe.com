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
 * Test the BlogController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogControllerTest
extends ControllerTestCase
{
    /**
     * A sample instance of the controller under test
     *
     * @return object An instance of Controller.
     */
    protected function sampleController() {
        return $this->create('BlogController');
    }

    /**
     * Ensure that it outputs Atom when asked to.
     *
     * @return void
     */
    public function testAtomOutput() {
        $req = new \Klein\Request();
        $req->format = 'atom';
        $controller = $this->create('BlogController', $req);

        $result = $this->runController($controller);

        $this->assertNotNull($result['output']);
        $this->assertRegexp('#^<\?xml.*</feed>$#s', $result['output']);
        $this->assertArrayHasKey('content-type', $result['headers']);
        $this->assertEquals(
            'application/atom+xml',
            $result['headers']['content-type']
        );
    }

    /**
     * Ensure that it outputs RSS when asked to.
     *
     * @return void
     */
    public function testRSSOutput() {
        $req = new \Klein\Request();
        $req->format = 'rss';
        $controller = $this->create('BlogController', $req);

        $result = $this->runController($controller);

        $this->assertNotNull($result['output']);
        $this->assertRegexp('#^<\?xml.*</rss>$#s', $result['output']);
        $this->assertArrayHasKey('content-type', $result['headers']);
        $this->assertEquals(
            'application/rss+xml',
            $result['headers']['content-type']
        );
    }
}

?>
