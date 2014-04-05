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

        if (is_file($localpath) && preg_match("#^$public_dir#", $localpath)) {
            $content_type = $this->detectMimeType($localpath);
            if ($content_type) {
                $this->response->header('Content-Type', $content_type);
            }
            $content = file_get_contents($localpath);
            $this->response->header('Content-Length', strlen($content));
            $this->setCacheHeaders();
            $this->response->body($content);
            return $this->response;
        }
        throw new Exception('File not found: ' . $uri, 404);
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

    /**
     * Detect the MIME type of a file, if possible.
     *
     * @param string $file The path to the file to check the type of.
     *
     * @return string The MIME type for the file, if known. Null otherwise.
     */
    private function detectMimeType($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
        if ($type === 'text/plain') {
            switch (pathinfo($file, PATHINFO_EXTENSION)) {
                case 'css':
                    $type = 'text/css';
                    break;
            }
        }
        return $type === false ? null : $type;
    }
}

?>
