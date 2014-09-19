<?php

/**
 * Handle requests for the "tech" page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TechController
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
        $this->model = new TechModel();
    }
}

?>
