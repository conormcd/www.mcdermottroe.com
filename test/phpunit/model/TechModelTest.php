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

    /**
     * Get a copy of TechModel for testing.
     *
     * @return TechModel An instance which can be tested.
     */
    protected function createTestObject() {
        return new TechModel('/css/default.css');
    }
}

?>
