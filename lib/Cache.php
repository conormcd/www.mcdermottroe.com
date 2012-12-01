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

/**
 * A facade over APC.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Cache {
    /**
     * Retrieve an entry from the cache.
     *
     * @param string $key The key for the cache entry.
     *
     * @return mixed      The value associated with the key, or null if it was
     *                    not found.
     */
    public static function get($key) {
        $result = null;
        $success = false;
        if (function_exists('apc_fetch')) {
            $result = apc_fetch($key, $success);
        }
        if (!$success) {
            return null;
        }
        return $result;
    }

    /**
     * Insert an entry into the cache.
     *
     * @param string $key   The key for the cache entry.
     * @param string $value The value for the cache entry.
     * @param string $ttl   The life time for the cache entry.
     *
     * @return void
     */
    public static function set($key, $value, $ttl) {
        if (function_exists('apc_store')) {
            apc_store($key, $value, $ttl);
        }
    }
}

?>
