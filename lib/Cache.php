<?php

/**
 * A facade over Memcached.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Cache {
    /** The connection to Memcached. */
    private static $_memcached = null;

    /**
     * Retrieve an entry from the cache.
     *
     * @param string $key The key for the cache entry.
     *
     * @return mixed      The value associated with the key, or null if it was
     *                    not found.
     */
    public static function get($key) {
        self::connect();

        $value = null;
        if (Environment::get('CACHE_ENABLE')) {
            $value = self::$_memcached->get($key);
            if (self::$_memcached->getResultCode() !== Memcached::RES_SUCCESS) {
                Logger::debug("Cache miss for $key");
                $value = null;
            }
        }
        return $value;
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
        if (Environment::get('CACHE_ENABLE')) {
            // The Memcached extension considers anything over 60*60*24*30 to
            // be a UNIX timestamp, so we need to adjust for that here.
            if ($ttl >= (60*60*24*30)) {
                $ttl = $ttl + time();
            }

            self::$_memcached->set($key, $value, $ttl);
            if (self::$_memcached->getResultCode() === Memcached::RES_SUCCESS) {
                Logger::debug("Storing value at $key for $ttl seconds");
            } else {
                Logger::error("Failed to store $key in Memcached");
            }
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
        if ($result === null || self::warming()) {
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

    /**
     * Connect to Memcached if we aren't already connected.
     *
     * @return void
     */
    private static function connect() {
        if (self::$_memcached === null) {
            self::$_memcached = new Memcached('www.mcdermottroe.com');
            self::$_memcached->addServer('127.0.0.1', 11211);
        }
    }

    /**
     * Detect if we're warming caches.
     *
     * @return boolean True if the CACHE_WARMING environment variable is set,
     *                 false otherwise.
     */
    private static function warming() {
        try {
            return Environment::get('CACHE_WARMING');
        } catch (Exception $e) {
            return false;
        }
    }
}

?>
