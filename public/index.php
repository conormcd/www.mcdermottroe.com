<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

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
