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
     * The URI as requested.
     */
    private $_uri;

    /**
     * Initialize.
     *
     * @param string $uri The URI for the request.
     */
    public function __construct($uri) {
        $this->_uri = $uri;
    }

    /**
     * The last modified time of the file, in HTTP date format.
     *
     * @return string The last modified time of the file, in HTTP date format.
     */
    public function lastModified() {
        return Time::http(filemtime($this->path()));
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
        $localpath = realpath($public_dir . $this->_uri);

        if (is_file($localpath) && preg_match("#^$public_dir#", $localpath)) {
            return $localpath;
        }
        throw new Exception('File not found: ' . $this->_uri, 404);
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5_file($this->path());
    }
}

?>
