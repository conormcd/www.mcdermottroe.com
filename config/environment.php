<?php

/**
 * This file can be included in order to load environment variables from a 
 * file. The file must be in JSON format.
 */

$CONFIG_LOCATIONS = array(
    '/etc/www.mcdermottroe.com.env',
);

foreach ($CONFIG_LOCATIONS as $env_file) {
    if (file_exists($env_file) && is_readable($env_file)) {
        $env = json_decode(file_get_contents($env_file), true);
        if ($env) {
            foreach ($env as $k => $v) {
                $_ENV[$k] = $v;
            }
        }
    }
}

?>
