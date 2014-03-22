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
}

?>
