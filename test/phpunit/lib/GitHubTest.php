<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Tests for GitHub.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GitHubTest
extends FactoryTestCase
{
    /**
     * Basic setup.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->class = get_class();
    }

    /**
     * Test getInstance with no environment variables.
     *
     * @return void
     */
    public function testGetInstanceNoEnv() {
        unset($_ENV['GITHUB_CLASS']);
        $instance = GitHub::getInstance();
        $this->assertNotNull($instance);
        $this->assertInstanceOf('GitHub', $instance);
    }

    /**
     * Test GitHub.repos.
     *
     * @return void
     */
    public function testRepos() {
        $timestamp = time() - 86400;

        // Fake response for this call.
        FakeHTTPClient::addResponse(
            "#^https://api.github.com/users/fake_github_user/repos#",
            JSON::encode(
                array(
                    array(
                        'name' => 'fake_repo',
                        'html_url' =>
                            'https://github.com/fake_github_user/fake_repo',
                        'description' => 'A fake GitHub repo.',
                        'private' => false,
                        'fork' => false,
                        'updated_at' => Time::dateISO8601($timestamp),
                    ),
                )
            )
        );

        $github = $this->getTestInstance();
        $this->assertEquals(
            array(
                array(
                    'name' => 'fake_repo',
                    'url' => 'https://github.com/fake_github_user/fake_repo',
                    'description' => 'A fake GitHub repo.',
                    'timestamp' => $timestamp,
                )
            ),
            $github->repos()
        );
    }

    /**
     * Generate a test GitHub object. We can't use GitHub::getInstance
     * since that's faked out so that other tests may run.
     *
     * @return GitHub An instance of the GitHub class.
     */
    private function getTestInstance() {
        return new GitHub(Environment::get('GITHUB_USER'));
    }
}

?>
