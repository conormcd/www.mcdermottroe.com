<?php

require_once dirname(__DIR__) . '/lib/autoloader.php';

/**
 * Cache warming for GitHub requests.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GitHubTask
extends Task
{
    /**
     * Run the task.
     *
     * @param array $args The command line arguments passed to this script.
     *
     * @return void
     */
    public function run($args) {
        if (count($args) > 0) {
            Logger::warning("Extra args passed to this task.");
        }
        $github = GitHub::getInstance();
        $github->repos();
    }
}

exit((new GitHubTask())->execute());

?>
