<?php

/**
 * Handle errors.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorController
extends Controller
{
    /**
     * Initialize.
     *
     * @param object $klein     The Klein main object.
     * @param object $request   The Request object from klein.
     * @param object $response  The Response object from klein.
     * @param object $exception An optional exception to get error data from.
     */
    public function __construct($klein, $request, $response, $exception = null) {
        $this->action = 'error';
        parent::__construct($klein, $request, $response);
        if ($exception !== null) {
            $code = $exception->getCode();
            if ($code < 400 || $code >= 600) {
                $code = 500;
            }
            $this->response->code($code);
            $this->model = $exception;
        } else {
            $this->response->code(404);
        }
    }
}

?>
