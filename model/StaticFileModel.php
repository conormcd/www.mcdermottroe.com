<?php

/**
 * Wrap static files so they look like other models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class StaticFileModel
extends Model
{
    /**
     * Initialise with some metadata.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->_metadata['og:type'] = 'website';
        $this->_metadata['og:title'] = array($this, 'path');
    }

    /**
     * This method should only be called when rendering a model via a template.
     * If that's happening on a static file then something really bad is
     * happening.
     *
     * @return string The path, as a dummy description.
     */
    public function description() {
        return $this->path();
    }

    /**
     * Detect the MIME type of the file, if possible.
     *
     * @return string The MIME type for the file, if known. Null otherwise.
     */
    public function mimeType() {
        $file = $this->path();
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

    /**
     * Get the path to the actual file.
     *
     * @return string The path to the file, if it exists and is in the public
     *                directory. If it doesn't exist or is not in the public
     *                directory then a 404 will be thrown.
     */
    public function path() {
        $public_dir = realpath(dirname(__DIR__) . '/public');
        $localpath = realpath($public_dir . $this->uri());

        if (is_file($localpath) && preg_match("#^$public_dir#", $localpath)) {
            return $localpath;
        }
        throw new Exception('File not found: ' . $this->uri(), 404);
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5_file($this->path());
    }

    /**
     * The last modified time of the file.
     *
     * @return int The UNIX epoch time for the last modification of the file.
     */
    public function timestamp() {
        return filemtime($this->path());
    }
}

?>
