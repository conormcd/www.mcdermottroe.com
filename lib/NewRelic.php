<?php

/**
 * Wrap New Relic.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class NewRelic {
    /**
     * Return the text of the JavaScript used at the start of a page for
     * measuring the front-end performance of the page.
     *
     * @return string The JavaScript that should be included in the header, or
     *                an empty string if New Relic is not available or
     *                disabled.
     */
    public static function javaScriptHeader() {
        return self::call('newrelic_get_browser_timing_header');
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
        return self::call('newrelic_get_browser_timing_footer');
    }

    /**
     * Call a New Relic function if it exists.
     *
     * @return mixed The return value of the function or empty string if the
     *               function does not exist.
     */
    private static function call() {
        $args = func_get_args();
        $function_name = array_shift($args);
        if (function_exists($function_name)) {
            return call_user_func_array($function_name, $args);
        }
        return '';
    }
}

?>
