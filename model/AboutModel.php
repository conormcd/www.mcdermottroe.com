<?php

/**
 * Interface to data to be shown on the about page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class AboutModel
extends Model
{
    /**
     * Initialize.
     */
    public function __construct() {
        parent::__construct();
        $this->_metadata['og:title'] = 'About me.';
        $this->_metadata['og:type'] = 'profile';
        $this->_metadata['profile:first_name'] = 'Conor';
        $this->_metadata['profile:last_name'] = 'McDermottroe';
        $this->_metadata['profile:gender'] = 'male';
    }

    /**
     * Describe the data shown on the /about page.
     *
     * @return string A description.
     */
    public function description() {
        return "About me.";
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5_file($this->templateFile());
    }

    /**
     * The last time this page was modified.
     *
     * @return int The UNIX epoch time for when this page was last changed.
     */
    public function timestamp() {
        return filemtime($this->templateFile());
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
     * The template file used for the content.
     *
     * @return string The path to the template file used for the content.
     */
    private function templateFile() {
        return dirname(__DIR__) . '/view/about.mustache';
    }
}

?>
