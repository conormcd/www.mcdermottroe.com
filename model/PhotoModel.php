<?php

/**
 * A single photo.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotoModel
extends Model
{
    private $_album;

    private $_photo_id;

    private $_index;

    private $_title;

    private $_images;

    private $_description;

    /**
     * Initialize a new photo.
     *
     * @param PhotoAlbumModel $album    The album to which this photo belongs.
     * @param int             $photo_id The ID of the photo.
     * @param int             $index    The position of the photo within the
     *                                  album.
     * @param array           $images   The different image URLs for this photo
     *                                  in an associative array where the keys
     *                                  are the names of the sizes and the
     *                                  values are the URLs to the images.
     */
    public function __construct($album, $photo_id, $index, $images) {
        parent::__construct();
        $this->_album = $album;
        $this->_photo_id = $photo_id;
        $this->_index = $index;
        $this->_images = $images;
        $this->_title = null;
        $this->_description = null;
        $this->_metadata['og:title'] = array($this, 'title');
        $this->_metadata['og:url'] = array($this, 'link');
    }

    /**
     * Get the ID for this photo.
     *
     * @return int The ID for this photo.
     */
    public function photoID() {
        return $this->_photo_id;
    }

    /**
     * Get/set the title/caption for the photo.
     *
     * @param string $title The title/caption of the photo.
     *
     * @return string The title of the photo.
     */
    public function title($title = null) {
        if ($title !== null) {
            $this->_title = $title;
        }
        return $this->_title;
    }

    /**
     * Get/set the description of this photo.
     *
     * @param string $description The new description for this photo.
     *
     * @return string The description of this photo.
     */
    public function description($description = null) {
        if ($description !== null) {
            $this->_description = $description;
        }
        return $this->_description;
    }

    /**
     * Get a short reference for this photo.
     *
     * @return string A short reference for this photo.
     */
    public function slug() {
        return join(
            '/',
            array(
                $this->_album->slug(),
                $this->_index + 1,
                1
            )
        );
    }

    /**
     * Get the "fullsize" size of this photo.
     *
     * @return string The URL for the fullsize size image for this photo.
     */
    public function fullsize() {
        return $this->_images['fullsize'];
    }

    /**
     * Get the "large" size of this photo.
     *
     * @return string The URL for the large size image for this photo.
     */
    public function large() {
        return $this->_images['large'];
    }

    /**
     * Get the "thumbnail" size of this photo.
     *
     * @return string The URL for the thumbnail size image for this photo.
     */
    public function thumbnail() {
        return $this->_images['thumbnail'];
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5(
            join(
                '',
                array(
                    $this->fullsize(),
                    $this->large(),
                    $this->photoID(),
                    $this->slug(),
                    $this->thumbnail(),
                    $this->title(),
                )
            )
        );
    }

    /**
     * Get the relative link to this photo.
     *
     * @return string The path to this photo.
     */
    public function link() {
        return '/photos/' . $this->slug();
    }
}

?>
