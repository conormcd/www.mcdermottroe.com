<?php

/*
 * Copyright (c) 2014, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * A single photo.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotoModel {
    private $_album;

    private $_photo_id;

    private $_index;

    private $_title;

    private $_images;

    /**
     * Initialize a new photo.
     *
     * @param PhotoAlbumModel $album    The album to which this photo belongs.
     * @param int             $photo_id The ID of the photo.
     * @param int             $index    The position of the photo within the
     *                                  album.
     * @param string          $title    The title/caption of the photo.
     * @param array           $images   The different image URLs for this photo
     *                                  in an associative array where the keys
     *                                  are the names of the sizes and the
     *                                  values are the URLs to the images.
     */
    public function __construct($album, $photo_id, $index, $title, $images) {
        $this->_album = $album;
        $this->_photo_id = $photo_id;
        $this->_index = $index;
        $this->_title = $title;
        $this->_images = $images;
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
     * Get the title/caption for the photo.
     *
     * @return string The title of the photo.
     */
    public function title() {
        return $this->_title;
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
}

?>
