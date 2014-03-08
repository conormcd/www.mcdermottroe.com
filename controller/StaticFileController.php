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
    }

    /**
     * Process GET requests.
     *
     * @return void
     */
    public function get() {
        $uri = $this->request->uri();
        $public_dir = realpath(dirname(__DIR__) . '/public');
        $localpath = realpath("$public_dir$uri");
        $filetypes = array(
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
        );

        if (file_exists($localpath)) {
            if (preg_match("#^$public_dir#", $localpath)) {
                foreach ($filetypes as $ext => $mime_type) {
                    if (preg_match("/\.$ext$/", $localpath)) {
                        $content_type = $mime_type;
                    }
                }

                if ($content_type) {
                    $this->response->header('Content-Type', $content_type);
                }
                print file_get_contents($localpath);
            }
        } else {
            throw new Exception('File not found', 404);
        }
    }
}

?>
