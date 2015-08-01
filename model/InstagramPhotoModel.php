<?php

/**
 * A single photo from Instagram.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class InstagramPhotoModel
extends Model
{
    private $_data;

    /**
     * Initialize a new Instagram photo.
     *
     * @param array $data The raw data from the Instagram API for this photo.
     */
    public function __construct($data) {
        parent::__construct();
        $this->_data = $data;
        $this->_metadata['og:title'] = array($this, 'title');
        $this->_metadata['og:url'] = array($this, 'link');
    }

    /**
     * Get the caption for the photo.
     *
     * @return string The caption of the photo.
     */
    public function description() {
        if ($this->_data['caption']) {
            return $this->_data['caption']['text'];
        }
        return "";
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5(var_export($this->_data, true));
    }

    /**
     * Get the biggest of the photo options.
     *
     * @return string The URL for the biggest image for this photo.
     */
    public function image() {
        return $this->_data['images']['standard_resolution']['url'];
    }

    /**
     * Because Mustache is a bit crap.
     *
     * @return boolean Always true.
     */
    public function isInstagramPhoto() {
        return true;
    }

    /**
     * The link to the image on Instagram.
     *
     * @return string The URL of this image on Instagram.
     */
    public function link() {
        return $this->_data['link'];
    }

    /**
     * The time the image was created.
     *
     * @return int The UNIX epoch time for the creation of this image.
     */
    public function timestamp() {
        return $this->_data['created_time'];
    }

    /**
     * A fake title for this image, since we're using the caption as a caption.
     *
     * @return string The human-readable form of the date this image was taken.
     */
    public function title() {
        return Time::day($this->_data['created_time']);
    }
}

?>
