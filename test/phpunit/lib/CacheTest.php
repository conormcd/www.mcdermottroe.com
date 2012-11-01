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
 * Tests for Cache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CacheTest
extends TestCase
{
    /**
     * Ensure that fetching a key that doesn't exist returns null.
     *
     * @return void
     */
    public function testGetBadKeyReturnsNull() {
        if (!function_exists('apc_store')) {
            $this->markTestIncomplete('APC is not installed/configured.');
        }
        $this->assertNull(Cache::get('this-does-not-exist'));
    }

    /**
     * Make sure that a key and value round-trips.
     *
     * @return void
     */
    public function testGetGoodKeyReturnsCorrectValue() {
        if (!function_exists('apc_store')) {
            $this->markTestIncomplete('APC is not installed/configured.');
        }
        Cache::set('test-key', 'test-value', 1);
        $this->assertEquals('test-value', Cache::get('test-key'));
    }
}

?>
