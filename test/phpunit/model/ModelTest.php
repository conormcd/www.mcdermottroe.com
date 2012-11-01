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
 * Tests for the Model abstract class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ModelTest
extends TestCase
{
    /**
     * Test that methods using the cache method from Model work as expected.
     *
     * @return void
     */
    public function testMethodWhichIsCached() {
        $testmodel = new TestModel();
        $this->assertEquals('Cached', $testmodel->methodWhichIsCached());
        $this->assertEquals('Cached', $testmodel->methodWhichIsCached());
    }

    /**
     * Test that uncached methods are unaffected by Model.
     *
     * @return void
     */
    public function testMethodWhichIsNotCached() {
        $testmodel = new TestModel();
        $this->assertEquals('Not Cached', $testmodel->methodWhichIsNotCached());
    }
}

/**
 * A test implementation of Model so that we can exercise it.
 *
 */
class TestModel
extends Model
{
    /**
     * A sample cached method.
     *
     * @return string The string "Cached".
     */
    public function methodWhichIsCached() {
        return $this->cache(
            'cached_method',
            1,
            function () {
                return 'Cached';
            }
        );
    }

    /**
     * A sample uncached method.
     *
     * @return string The string "Not Cached".
     */
    public function methodWhichIsNotCached() {
        return 'Not Cached';
    }
}

?>
