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
     * @param object $klein    The Klein main object.
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($klein, $request, $response) {
        $this->action = 'tech';
        parent::__construct($klein, $request, $response);

        $this->model = new TechModel();
    }
}

?>
