<?php

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
