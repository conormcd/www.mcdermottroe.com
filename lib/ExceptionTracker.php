<?php

/**
 * An interface for exception tracking.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class ExceptionTracker {
    /**
     * We don't really want multiple copies of the exception tracker lying
     * around. Yes, I know singletons are bad.
     */
    private static $_instance = null;

    /**
     * Get an instance of the configured exception tracker.
     *
     * @return ExceptionTracker An instance of this class.
     */
    public static function getInstance() {
        if (!self::$_instance) {
            $tracker_name = Environment::get('EXCEPTION_TRACKER');
            self::$_instance = $tracker_name::getInstance();
        }
        return self::$_instance;
    }

    /**
     * Capture an exception which should be centrally tracked.
     *
     * @param Exception $exception The exception to track.
     *
     * @return void
     */
    public abstract function captureException($exception);

    /**
     * Tell the tracker to register itself as an error handler and exception
     * handler.
     *
     * @return void
     */
    public abstract function registerHandlers();
}

?>
