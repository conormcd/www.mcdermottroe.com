<?php

/**
 * Give a cleaner interface to environment variables.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Environment {
    /** The config files to load. */
    private static $_config_files = array('/etc/www.mcdermottroe.com.env' => 1);

    /**
     * Get the value of an environment variable.
     *
     * @param string $var The name of the environment variable.
     *
     * @return mixed The value of the environment variable.
     */
    public static function get($var) {
        self::load();
        if (!array_key_exists($var, $_ENV)) {
            throw new Exception("ENV[$var] is not set.");
        }
        return $_ENV[$var];
    }

    /**
     * Load the values of some environment variables from a series of
     * configuration files.
     *
     * @return void
     */
    public static function load() {
        if (!array_key_exists('ENVIRONMENT_CANARY', $_ENV)) {
            foreach (self::configFiles() as $env_file) {
                if (is_readable($env_file)) {
                    $env = JSON::decode(file_get_contents($env_file));
                    if ($env) {
                        foreach ($env as $k => $v) {
                            $_ENV[$k] = $v;
                        }
                    }
                }
            }
            $_ENV['ENVIRONMENT_CANARY'] = 'set';
        }
    }

    /**
     * Get the config files to scan for environment variables.
     *
     * @return array An array of file paths.
     */
    public static function configFiles() {
        return array_keys(self::$_config_files);
    }
}
?>
