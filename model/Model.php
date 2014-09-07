<?php

/**
 * Common functionality for all models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Model {
    /**
     * The New Relic JavaScript monitoring code to be inserted in the footer.
     *
     * @return string The JavaScript to be inserted in the footer or an empty
     *                string if the New Relic extension is not loaded.
     */
    public function newRelicJSFooter() {
        return NewRelic::javaScriptFooter();
    }

    /**
     * The New Relic JavaScript monitoring code to be inserted in the header.
     *
     * @return string The JavaScript to be inserted in the header or an empty
     *                string if the New Relic extension is not loaded.
     */
    public function newRelicJSHeader() {
        return NewRelic::javaScriptHeader();
    }

    /**
     * The maximum amount of time this model may be cached for.
     *
     * @return int The maximum number of seconds this model should be cached
     *             for. Only positive integers will be used as cache lifetimes,
     *             anything else will result in instantaneous expiration.
     */
    public function ttl() {
        return 0;
    }

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
