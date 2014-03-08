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
     * @param object $klein    The Klein main object.
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($klein, $request, $response) {
        $this->action = 'front_page';
        $this->model = new FrontPageModel($request->page, $request->per_page);
        parent::__construct($klein, $request, $response);
    }
}

?>
