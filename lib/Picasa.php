<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
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
 * A facade for Picasa.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Picasa {
    /** The XML Schema URL for gphoto: elements in the Picasa XML. */
    const GPHOTO_SCHEMA = 'http://schemas.google.com/photos/2007';

    /** The XML Schema URL for media: elements in the Picasa XML. */
    const MEDIA_SCHEMA = 'http://search.yahoo.com/mrss/';

    /** The Picasa user. */
    private $user;

    /** Albums which should not appear in the output. */
    private $excluded_albums;

    /**
     * Initialize.
     *
     * @param string $user            The Picasa user to fetch data for.
     * @param array  $excluded_albums The names of any albums that should not 
     *                                appear in the output.
     */
    public function __construct($user, $excluded_albums = null) {
        $this->user = $user;
        if ($excluded_albums) {
            $this->excluded_albums = $excluded_albums;
        } else {
            $this->excluded_albums = array();
        }
    }

    /**
     * Get albums from Picasa.
     *
     * @return array The list of albums.
     */
    public function albums() {
        $albums = array();
        $xml = $this->feed();
        foreach ($xml->entry as $entry) {
            $gphoto = $entry->children(Picasa::GPHOTO_SCHEMA);
            if ($gphoto->numphotos > 0) {
                $media = $entry->children(Picasa::MEDIA_SCHEMA);
                $thumbnail = $media->group->thumbnail->attributes();
                if (!in_array("{$gphoto->name}", $this->excluded_albums)) {
                    $albums[] = array(
                        'name' => "{$gphoto->name}",
                        'title' => "{$media->group->title}",
                        'timestamp' => "{$gphoto->timestamp}",
                        'thumbnail' => "{$thumbnail['url']}",
                    );
                }
            }
        }
        return $albums;
    }

    /**
     * Get the details for all the photos in an album.
     *
     * @param string $album The name of the album to fetch photos from.
     *
     * @return array A list of the photos in that album.
     */
    public function photos($album) {
        $photos = array();
        $xml = $this->feed($album);
        foreach ($xml->entry as $entry) {
            $gphoto = $entry->children(Picasa::GPHOTO_SCHEMA);
            $photos[] = array(
                'title' => "{$entry->summary}",
                'timestamp' => "{$gphoto->timestamp}",
                'thumbnail' => "{$entry->content['src']}",
            );
        }
        return $photos;
    }

    /**
     * Fetch the XML from a particular feed.
     *
     * @param string $album The name of the album to fetch photo entries from.
     *                      If this is omitted or falsey then the list of
     *                      albums will be fetched instead.
     *
     * @return object       A SimpleXMLElement containing the data from the
     *                      feed requested.
     */
    private function feed($album = null) {
        $url = "https://picasaweb.google.com/data/feed/api/user/{$this->user}/";
        if ($album) {
            $url .= "album/$album/";
        }

        $key = "photos_feed_" . md5($url);
        $xml = Cache::get($key);
        if ($xml === null) {
            $xml = file_get_contents($url);
            Cache::set($key, $xml, 0);
        }

        return new SimpleXMLElement($xml);
    }
}

?>
