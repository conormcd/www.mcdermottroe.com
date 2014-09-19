<?php

/**
 * Handle requests for the front page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FrontPageController
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
        $this->model = new FrontPageModel($request->page, $request->per_page);
    }
}

?>
