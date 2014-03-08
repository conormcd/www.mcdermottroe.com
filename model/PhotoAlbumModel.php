<?php

/**
 * An album or set of photos.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotoAlbumModel {
    private $_flickr;

    private $_album_id;

    private $_title;

    private $_slug;

    private $_thumbnail_id;

    private $_timestamp;

    private $_timestamp_create;

    /**
     * Initialize the albums.
     *
     * @param Flickr $flickr    The Flickr object which we can use for further
     *                          requests.
     * @param int    $album_id  The ID of the album.
     * @param string $title     The title of the album.
     * @param int    $timestamp The UNIX epoch time for when the album was
     *                          created.
     * @param int    $thumbnail The ID of the photo which is to be used as the
     *                          thumbnail for the album.
     */
    public function __construct($flickr, $album_id, $title, $timestamp, $thumbnail) {
        $this->_flickr = $flickr;
        $this->_album_id = $album_id;
        $this->_title = $title;
        $this->_timestamp_create = $timestamp;
        $this->_thumbnail_id = $thumbnail;
    }

    /**
     * Because Mustache can't do real code.
     *
     * @return boolean Always true.
     */
    public function isPhotoAlbum() {
        return true;
    }

    /**
     * Get the ID for the album.
     *
     * @return int The ID of this album.
     */
    public function albumID() {
        return $this->_album_id;
    }

    /**
     * Get the title of the photo album.
     *
     * @return string The title of the photo album.
     */
    public function title() {
        return $this->_title;
    }

    /** Try and infer the real dates of the photos from the album title.
     *
     * @return array A timestamp for the album.
     */
    public function timestamp() {
        if (!$this->_timestamp) {
            $mon = '[A-Z][a-z][a-z]';
            $year = '\d\d\d\d';

            $patterns = array(
                "/($mon $year) - $mon $year$/" => array(1),
                "/($mon)(?:\/| - )$mon ($year)$/" => array(1, 2),
                "/\b($mon $year)$/" => array(1),
                "/\b$year$/" => array(1)
            );

            $title_time = false;
            foreach ($patterns as $pattern => $groups) {
                if (preg_match($pattern, $this->_title, $match)) {
                    $groups_text = array();
                    foreach ($groups as $group_number) {
                        $groups_text[] = $match[$group_number];
                    }
                    $title_time = strtotime(join(' ', $groups_text));
                    break;
                }
            }

            if ($title_time === false) {
                $this->_timestamp = $this->_timestamp_create;
            }
        }

        return $this->_timestamp;
    }

    /**
     * Get the photos from this album.
     *
     * @return array An array of PhotoModel objects for each of the photos in
     *               this album.
     */
    public function photos() {
        return $this->_flickr->getPhotos($this);
    }

    /**
     * Get a URL safe version of the album's title.
     *
     * @return string A string containing only alphanumeric characters which
     *                should closely resemble the album title.
     */
    public function slug() {
        if (!$this->_slug) {
            $this->_slug = '';
            foreach (preg_split('/[^A-Za-z0-9]+/', $this->title()) as $part) {
                $this->_slug .= ucfirst($part);
            }
        }
        return $this->_slug;
    }

    /**
     * Get a PhotoModel for the thumbnail image for this album.
     *
     * @return PhotoModel The image that is the thumbnail for this album.
     */
    public function thumbnail() {
        foreach ($this->photos() as $photo) {
            if ($photo->photoID() == $this->_thumbnail_id) {
                return $photo;
            }
        }
    }
}

?>
