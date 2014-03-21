<?php

/**
 * Fake out Sentry.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeSentry
extends ExceptionTracker
{
    public $lastException;

    public $handlersRegistered;

    /**
     * Create a new instance of this class.
     *
     * @return FakeSentry An instance of this class.
     */
    public static function getInstance() {
        return new FakeSentry();
    }

    /**
     * {@inheritdoc}
     *
     * @param Exception $exception See {@link ExceptionTracker#captureException()}
     *
     * @return void
     */
    public function captureException($exception) {
        $this->lastException = $exception;
    }


    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function registerHandlers() {
        $this->handlersRegistered = true;
    }
}

?>
