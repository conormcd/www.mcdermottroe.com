<?php

// This is where the site is notionally based.
date_default_timezone_set('Europe/Dublin');

// We need this for the config for loading Sentry (if appropriate)
require_once dirname(__DIR__) . '/config/environment.php';

// Load Sentry for trapping exceptions and errors
if (isset($_ENV['SENTRY_DSN'])) {
    // Include all the necessary bits for Sentry
    $raven_lib = dirname(__DIR__) . '/lib/raven-php/lib';
    include_once "$raven_lib/Raven/Autoloader.php";
    Raven_Autoloader::register();

    // Get the connection to Sentry
    $_ENV['SENTRY'] = new Raven_Client($_ENV['SENTRY_DSN']);

    // Install the error handlers for stray exceptions and errors
    $error_handler = new Raven_ErrorHandler($_ENV['SENTRY']);
    set_error_handler(array($error_handler, 'handleError'));
    set_exception_handler(array($error_handler, 'handleException'));
}

require_once dirname(__DIR__) . '/config/routes.php';
require_once dirname(__DIR__) . '/lib/autoloader.php';

(new Router($ROUTES))->dispatch();

?>
