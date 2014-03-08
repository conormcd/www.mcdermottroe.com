<?php

/**
 * Common functionality for all models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Model {
    /**
     * Wrap a chunk of code in some caching.
     *
     * @param string   $key      The key for the cache entry.
     * @param int      $ttl      The life time for the cache entry.
     * @param callable $callable The code to wrap in caching.
     * @param array    $args     Arguments to pass to the cached function.
     *
     * @return mixed             The result of the wrapped code, possibly from
     *                           the cache.
     */
    protected function cache($key, $ttl, $callable, $args = array()) {
        $result = Cache::get($key);
        if ($result === null) {
            $result = call_user_func_array($callable, $args);
            Cache::set($key, $result, $ttl);
        }
        return $result;
    }
}

?>
