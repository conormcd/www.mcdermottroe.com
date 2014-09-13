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
     * Initialize.
     *
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($request, $response) {
        $this->action = 'about';
        parent::__construct($request, $response);
    }

    /**
     * Suggest that the about page be cached for an hour.
     *
     * @return array See Controller#cacheControl.
     */
    protected function cacheControl() {
        return array('public' => true, 'max-age' => 3600);
    }
}

?>
