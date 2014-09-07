<?php

/**
 * Wrap New Relic.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NewRelic {
    private static $_disabled = false;

    /**
     * Enable New Relic. New Relic is on by default so you don't need to call
     * this unless you previously called disable.
     *
     * @return void
     */
    public static function enable() {
        self::$_disabled = false;
    }

    /**
     * Disable New Relic.
     *
     * @return void
     */
    public static function disable() {
        self::$_disabled = true;
    }

    /**
     * Return the text of the JavaScript used at the start of a page for
     * measuring the front-end performance of the page.
     *
     * @return string The JavaScript that should be included in the header, or
     *                an empty string if New Relic is not available or
     *                disabled.
     */
    public static function javaScriptHeader() {
        if (self::enabled()) {
            return newrelic_get_browser_timing_header();
        }
        return '';
    }

    /**
     * Return the text of the JavaScript used at the end of a page for
     * measuring the front-end performance of the page.
     *
     * @return string The JavaScript that should be included in the footer, or
     *                an empty string if New Relic is not available or
     *                disabled.
     */
    public static function javaScriptFooter() {
        if (self::enabled()) {
            return newrelic_get_browser_timing_footer();
        }
        return '';
    }

    /**
     * Name a transaction based on the controller and action.
     *
     * @param Controller $controller The controller being used for the current
     *                               page.
     * @param string     $action     The name of the action being executed.
     *
     * @return void
     */
    public static function transaction($controller, $action) {
        if (self::enabled()) {
            newrelic_name_transaction(
                preg_replace('/Controller$/', '', get_class($controller)) .
                '/' .
                $action
            );
        }
    }

    /**
     * Check if New Relic is enabled.
     *
     * @return boolean True if New Relic is both enabled and available.
     */
    private static function enabled() {
        return !self::$_disabled && extension_loaded('newrelic');
    }
}

?>
