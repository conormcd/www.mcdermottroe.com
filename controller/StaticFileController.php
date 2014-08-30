<?php

/**
 * Handle requests for static files. This should only ever be hit in
 * development since the web server should serve all static file requests
 * directly.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class StaticFileController
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
        $this->action = 'staticfile';
        parent::__construct($klein, $request, $response);

        $this->model = new StaticFileModel($this->request->uri());
    }

    /**
     * Render the contents of the file.
     *
     * @return string The contents of the file.
     */
    protected function content() {
        return file_get_contents($this->model()->path());
    }

    /**
     * Expire static resources in the future.
     *
     * @return array See Controller->cacheControl().
     */
    protected function cacheControl() {
        return array(
            'public' => true,
            'max-age' => 3600,
        );
    }
}

?>
