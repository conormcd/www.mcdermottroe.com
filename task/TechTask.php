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
     * @return void
     */
    public function run() {
        $model = new TechModel();
        $model->gitHubRepos();
    }
}

exit((new TechTask())->execute());

?>
