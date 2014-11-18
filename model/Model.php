<?php

/**
 * Common functionality for all models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Model {
    /**
     * The value that should be used as an ETag for any page referencing this
     * model.
     *
     * @return string A string that can be used as an identifier of the content
     *                represented by this model.
     */
    public abstract function eTag();

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
}

?>
