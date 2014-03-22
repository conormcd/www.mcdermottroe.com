<?php

/**
 * A fake version of the GitHub class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeGitHub
extends GitHub
{
    /**
     * Init.
     *
     * @param string $user The user to fetch repos for.
     */
    public function __construct($user) {
        $this->_user = $user;
    }

    /**
     * List the fake repos.
     *
     * @return array Just like GitHub.repos
     */
    public function repos() {
        return array(
            array(
                'name' => 'fake_repo',
                'url' => "https://github.com/{$this->_user}/fake_repo",
                'description' => 'This is a fake repo',
            ),
        );
    }
}

?>
