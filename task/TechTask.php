<?php

require_once dirname(__DIR__) . '/lib/autoloader.php';

/**
 * Cache warming for the /tech endpoint.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TechTask
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
        $model = new TechModel();
        $model->gitHubRepos();
    }
}

exit((new TechTask())->execute());

?>
