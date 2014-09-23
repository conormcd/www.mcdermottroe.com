<?php

/**
 * Handle requests for the "about" page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class AboutController
extends Controller
{
    /**
     * Suggest that the about page be cached for an hour.
     *
     * @return array See Controller#cacheControl.
     */
    protected function cacheControl() {
        return array('public' => true, 'max-age' => 86400 + rand(0, 3600));
    }
}

?>
