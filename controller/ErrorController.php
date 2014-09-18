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
     * @param object $request   The Request object from klein.
     * @param object $response  The Response object from klein.
     * @param object $exception An optional exception to get error data from.
     */
    public function __construct($request, $response, $exception = null) {
        $this->action = 'error';
        parent::__construct($request, $response);
        if ($exception !== null) {
            $this->model = new ErrorModel($exception);
        } else {
            $this->model = new ErrorModel('File not found', 404);
        }
        $response->unlock();
        $response->code($this->model->code());
    }
}

?>
