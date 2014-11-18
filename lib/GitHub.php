<?php

/**
 * A highly-simpliefied interface to GitHub
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GitHub {
    /**
     * Get an instance of this class.
     *
     * @return GitHub An instance of GitHub.
     */
    public static function getInstance() {
        try {
            $class_name = Environment::get('GITHUB_CLASS');
        } catch (Exception $e) {
            $class_name = get_class();
        }
        return new $class_name(Environment::get('GITHUB_USER'));
    }

    /**
     * Init.
     *
     * @param string $github_user The user whose repos should be listed.
     */
    public function __construct($github_user) {
        $this->_user = $github_user;
        $this->_http_client = HTTPClient::getInstance();
    }

    /**
     * List the public, source repositories owned by the current user.
     *
     * @return array An array of associative arrays, each of which is a repo.
     */
    public function repos() {
        $user = $this->_user;
        $repos = array();
        foreach ($this->get("/users/$user/repos") as $repo) {
            if (!($repo['private'] || $repo['fork'])) {
                $repos[] = array(
                    'name' => $repo['name'],
                    'url' => $repo['html_url'],
                    'description' => $repo['description'],
                );
            }
        }
        return $repos;
    }

    /**
     * Execute a GitHub GET request.
     *
     * @param string $path The relative API path to the URL to get.
     *
     * @return array The decoded form of the JSON which was returned.
     */
    private function get($path) {
        $client = $this->_http_client;
        return Cache::run(
            'GITHUB_API_REQUEST_' . md5($path),
            3600 + rand(0, 300),
            function () use ($client, $path) {
                return JSON::decode(
                    $client->get("https://api.github.com$path")
                );
            }
        );
    }
}

?>
