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
        parent::__construct($request, $response);
        $this->model = new AboutModel();
    }

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
