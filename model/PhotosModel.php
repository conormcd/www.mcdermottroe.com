<?php

/*
 * Copyright (c) 2012-2014, Conor McDermottroe
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
 * Model for wrapping accesses to Flickr.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotosModel
extends PageableModel
{
    /**
     * The default limit on the number of photos to be shown on each page of
     * results.
     */
    const PHOTOS_PER_PAGE = 12;

    /** The name of the album to show. */
    public $album;

    /** The photo provider we're using for a backend is Flickr. */
    private $_flickr;

    /**
     * Create a facade over Flickr.
     *
     * @param string $album    The name of the album to fetch, null if you want
     *                         to receive a page of thumbnails for albums.
     * @param int    $start    The index (1-based) of the first entry to
     *                         show on the page.
     * @param int    $per_page The number of entries to display per page.
     */
    public function __construct($album = null, $start = null, $per_page = null) {
        if ($album) {
            $per_page = $per_page ? $per_page : $this->getDefaultPerPage();
        } else {
            $per_page = -1;
        }
        if ($per_page > 0) {
            $page = ceil($start / $per_page);
        } else {
            $page = 1;
        }
        parent::__construct($page, $per_page);
        $this->album = $album;
        $this->_flickr = new Flickr(
            $_ENV['FLICKR_API_KEY'],
            $_ENV['FLICKR_API_SECRET'],
            $_ENV['FLICKR_API_USER']
        );
    }

    /**
     * Override the default page size from PageableModel.
     *
     * @return The default size of a page.
     */
    public function getDefaultPerPage() {
        return self::PHOTOS_PER_PAGE;
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#all()}.
     */
    public function all() {
        if ($this->album) {
            return $this->photos($this->album);
        } else {
            return $this->albums();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#next()}.
     */
    public function next() {
        $link = "";
        if ($this->album && $this->page < $this->numPages()) {
            $link = $this->generateLink(
                $this->album,
                ($this->page * $this->per_page) + 1,
                $this->per_page
            );
        }
        return $link;
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#nextLabel()}.
     */
    public function nextLabel() {
        return "Next";
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#previous()}.
     */
    public function previous() {
        $link = "";
        if ($this->album && $this->page > 1) {
            $link = $this->generateLink(
                $this->album,
                ((($this->page - 1) * $this->per_page) - $this->per_page) + 1,
                $this->per_page
            );
        }
        return $link;
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#previousLabel()}.
     */
    public function previousLabel() {
        return "Previous";
    }

    /**
     * Get the list of albums from Flickr.
     *
     * @return array The list of albums.
     */
    public function albums() {
        return $this->_flickr->getAlbums();
    }

    /**
     * Get the details for all the photos in an album.
     *
     * @param string $album The name of the album to fetch photos from.
     *
     * @return array A list of the photos in that album.
     */
    public function photos($album) {
        return $this->_flickr->getPhotos($this->_flickr->getAlbum($album));
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#link()}.
     */
    protected function link() {
        return $this->generateLink(
            $this->album,
            (($this->page - 1) * $this->per_page) + 1,
            $this->per_page
        );
    }

    /**
     * Create a link
     *
     * @param string $album    The album to link to, if appropriate.
     * @param int    $page     The page to link to, if appropriate.
     * @param int    $per_page The number of results per page on the
     *                         destination page, if apppropriate.
     *
     * @return string A relative URL to the page requested.
     */
    private function generateLink($album, $page, $per_page) {
        if ($page == 1) {
            $page = null;
        }
        if ($per_page == $this->getDefaultPerPage()) {
            $per_page = null;
        }
        $parts = array_filter(
            array('photos', $album, $page, $per_page)
        );
        return '/' . join('/', $parts);
    }
}

?>
