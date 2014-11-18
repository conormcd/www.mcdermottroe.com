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
        if (Environment::get('CACHE_ENABLE') && function_exists('apc_fetch')) {
            $result = apc_fetch($key, $success);
            if (!$success) {
                Logger::debug("Cache miss for $key");
            }
        }
        return $success ? $result : null;
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
        if (Environment::get('CACHE_ENABLE') && function_exists('apc_store')) {
            apc_store($key, $value, $ttl);
            Logger::debug("Storing value at $key for $ttl seconds");
        }
    }

    /**
     * Empty the cache.
     *
     * @return void
     */
    public static function clear() {
        if (Environment::get('CACHE_ENABLE') && function_exists('apc_clear_cache')) {
            apc_clear_cache('user');
        }
    }

    /**
     * Run some code and cache the result.
     *
     * @param string   $key      The key for the cache entry.
     * @param int      $ttl      The life time for the cache entry.
     * @param callable $callable The code to run and cache.
     * @param array    $args     Arguments to pass to the cached function.
     *
     * @return mixed             The result of the wrapped, possibly from the
     *                           cache.
     */
    public static function run($key, $ttl, $callable, $args = array()) {
        $result = self::get($key);
        if ($result === null) {
            $start = microtime(true);
            $result = call_user_func_array($callable, $args);
            Logger::debug(
                "Value recomputed for $key in " .
                (microtime(true) - $start) .
                " seconds."
            );
            self::set($key, $result, $ttl);
        }
        return $result;
    }
}

?>
