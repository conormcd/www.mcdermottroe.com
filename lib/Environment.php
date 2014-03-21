<?php

/**
 * Give a cleaner interface to environment variables.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Environment {
    /** Whether or not the .env files have been loaded. */
    private static $_loaded = false;

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
        if (!self::$_loaded) {
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

            self::$_loaded = true;
        }
    }

    /**
     * Add a config file location where a JSON-encoded set of environment
     * variables can be found.
     *
     * @param string $file THe path to the file to load.
     *
     * @return void
     */
    public static function addConfigFile($file) {
        self::$_config_files[$file] = 1;
        self::$_loaded = false;
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
