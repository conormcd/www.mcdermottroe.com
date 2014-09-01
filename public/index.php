<?php

// This is where the site is notionally based.
date_default_timezone_set('Europe/Dublin');

require_once dirname(__DIR__) . '/config/routes.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/lib/autoloader.php';

// Make sure the exception tracker is turned on.
ExceptionTracker::getInstance()->registerHandlers();

(new Router($ROUTES))->dispatch();

?>
