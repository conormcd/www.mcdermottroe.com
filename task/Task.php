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
        try {
            $this->run();
        } catch (Exception $e) {
            print $e->getMessage();
            return 1;
        }
        return 0;
    }

    /**
     * Run the task.
     *
     * @return void
     */
    public abstract function run();
}

?>
