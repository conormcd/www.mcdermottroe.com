<?php

/**
 * Common functionality for all tasks.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Task {
    /**
     * Set up the task
     *
     */
    public function __construct() {
        $_ENV['CACHE_WARMING'] = true;
    }

    /**
     * Main entry point for the task.
     *
     * @return void
     */
    public function execute() {
        global $argv;

        try {
            $args = array();
            if (count($argv) > 1) {
                $args = array_slice($argv, 1);
            }
            $this->run($args);
        } catch (Exception $e) {
            print $e->getMessage();
            return 1;
        }
        return 0;
    }

    /**
     * Run the task.
     *
     * @param array $args The arguments passed to the script.
     *
     * @return void
     */
    public abstract function run($args);
}

?>
