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
     * Name a transaction based on the controller and action.
     *
     * @param Controller $controller The controller being used for the current
     *                               page.
     * @param string     $action     The name of the action being executed.
     *
     * @return void
     */
    public static function transaction($controller, $action) {
        self::call(
            'newrelic_name_transaction',
            preg_replace('/Controller$/', '', get_class($controller)) .
            '/' .
            $action
        );
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
