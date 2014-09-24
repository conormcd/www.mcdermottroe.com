<?php

/**
 * A facade over syslog.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Logger {
    /**
     * Log an error message.
     *
     * @param string $message The text of the error message.
     *
     * @return void
     */
    public static function error($message) {
        self::log('E', $message);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The text of the message.
     *
     * @return void
     */
    public static function warning($message) {
        self::log('W', $message);
    }

    /**
     * Log an informational message.
     *
     * @param string $message The text of the message.
     *
     * @return void
     */
    public static function info($message) {
        self::log('I', $message);
    }

    /**
     * Log a debug message.
     *
     * @param string $message The text of the message.
     *
     * @return void
     */
    public static function debug($message) {
        self::log('D', $message);
    }

    /**
     * Actually log the message.
     *
     * @param string $level   One of ['D', 'E', 'I', 'W'] for debug, error,
     *                        info and warning respectively.
     * @param string $message The message to log.
     *
     * @return void
     */
    private static function log($level, $message) {
        $levels = array(
            'D' => LOG_DEBUG,
            'E' => LOG_ERR,
            'I' => LOG_INFO,
            'W' => LOG_WARNING,
        );
        $ident = join(' ', array($level, self::caller(debug_backtrace(0))));
        openlog($ident, LOG_ODELAY, LOG_LOCAL0);
        syslog($levels[$level], $message);
        closelog();
    }

    /**
     * Format the caller of a function based on a supplied backtrace.
     *
     * @param array $backtrace The result of calling debug_backtrace.
     *
     * @return string A human-readable description of where the log call was
     *                made from.
     */
    private static function caller($backtrace) {
        $www_root = dirname(__DIR__);

        $file = preg_replace("#^$www_root/#", "", $backtrace[1]['file']);
        $line = $backtrace[1]['line'];

        $src = '';
        if (count($backtrace) > 2) {
            if (array_key_exists('class', $backtrace[2])) {
                $src = join(
                    '',
                    array(
                        $backtrace[2]['class'],
                        $backtrace[2]['type'],
                        $backtrace[2]['function']
                    )
                );
            } else {
                $src = $backtrace[2]['function'];
            }
        }

        $caller = "$file:$line";
        if ($src) {
            $caller .= " $src";
        }

        return $caller;
    }
}

?>
