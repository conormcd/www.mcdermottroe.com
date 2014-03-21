<?php

/**
 * A shim to make the Sentry client library behave like an ExceptionTracker.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Sentry
extends ExceptionTracker
{
    private $_raven_client;

    /**
     * Get an instance of this class.
     *
     * @return Sentry An instance of this class.
     */
    public static function getInstance() {
        return new Sentry(Environment::get('SENTRY_DSN'));
    }

    /**
     * Create a new Sentry.
     *
     * @param string $dsn The Sentry DSN to connect to.
     */
    public function __construct($dsn) {
        $raven_lib = dirname(__DIR__) . '/lib/raven-php/lib';
        include_once "$raven_lib/Raven/Autoloader.php";
        Raven_Autoloader::register();

        $this->_raven_client = new Raven_Client($dsn);
    }

    /**
     * {@inheritdoc}
     *
     * @param Exception $exception See {@link ExceptionTracker#captureException()}
     *
     * @return void
     */
    public function captureException($exception) {
        $this->_raven_client->captureException($exception);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function registerHandlers() {
        $error_handler = new Raven_ErrorHandler($this->_raven_client);
        set_error_handler(array($error_handler, 'handleError'));
        set_exception_handler(array($error_handler, 'handleException'));
    }
}

?>
