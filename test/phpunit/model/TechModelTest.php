<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the TechModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TechModelTest
extends ModelTestCase
{
    /**
     * Test TechModel.gitHubRepos.
     *
     * @return void
     */
    public function testGitHubRepos() {
        $instance = new TechModel();
        $repos = $instance->gitHubRepos();
        $this->assertNotNull($repos);
        $this->assertNotEmpty($repos);
    }
}

?>
