<?php

/**
 * Interface to data to be shown on the tech page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TechModel
extends Model
{
    private $_github;

    /**
     * Init.
     */
    public function __construct() {
        $this->_github = GitHub::getInstance();
    }

    /**
     * Fetch all the GitHub repos which should be shown.
     *
     * @return array An array of associative arrays, each of which is a repo.
     */
    public function gitHubRepos() {
        return $this->_github->repos();
    }

    /**
     * Trigger some caching.
     *
     * @return int The maximum number of seconds pages using this model should
     *             be cached for.
     */
    public function ttl() {
        return 86400 + rand(0, 3600);
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5(var_export($this->gitHubRepos(), true));
    }
}

?>
