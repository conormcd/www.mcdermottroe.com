<?php

/**
 * Handle requests for the Google Site Map
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class GoogleSiteMapController
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
        $this->model = new GoogleSiteMapModel();
        $this->content_type = 'application/xml';
    }
}

?>
