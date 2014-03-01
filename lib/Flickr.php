<?php

/*
 * Copyright (c) 2013-2014, Conor McDermottroe
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
 * A simple interface to Flickr.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Flickr {
    private $_api_key;

    private $_api_secret;

    private $_username;

    /**
     * Create a new Flickr interface. You can find the credentials in the
     * environment if you include environment.php.
     *
     * @param string $key    The API key for Flickr.
     * @param string $secret The API secret for Flickr.
     * @param string $user   The Flickr user from whom to copy the photos.
     */
    public function __construct($key, $secret, $user) {
        $this->_api_key = $key;
        $this->_api_secret = $secret;
        $this->_username = $user;
    }

    /**
     * Get a list of the sets owned by the current user.
     *
     * @return An array of arrays, each of which contains album details.
     */
    public function getAlbums() {
        $key = 'FLICKR_ALBUMS';
        $albums = Cache::get($key);
        if (!$albums) {
            $albums = array();
            $response = $this->photosets->getList(
                array('user_id' => $this->getCurrentUserNSID())
            );
            foreach ($response['photosets']['photoset'] as $set) {
                $title = $set['title']['_content'];
                $short_title = '';
                foreach (preg_split('/[^A-Za-z0-9]+/', $title) as $part) {
                    $short_title .= ucfirst($part);
                }
                $thumbnail = $this->getThumbnail($set['primary']);
                $albums[] = array(
                    'title' => $title,
                    'short_title' => $short_title,
                    'thumbnail' => $thumbnail,
                    'set' => $set['id']
                );
            }
            Cache::set($key, $albums, 3600);
        }
        return $albums;
    }

    /**
     * Get a single set by name.
     *
     * @param string $album_short_name The short name of the set to fetch.
     *
     * @return Return a single entry from getAlbums, searched by short name.
     */
    public function getAlbum($album_short_name) {
        $albums = $this->getAlbums();
        foreach ($albums as $album) {
            if ($album['short_title'] == $album_short_name) {
                return $album;
            }
        }
        throw new Exception(
            "Couldn't find the Flickr set \"$album_short_name\"",
            404
        );
    }

    /**
     * Get a list of the photos in a set.
     *
     * @param string $album The album from which to retrieve the photos.
     *
     * @return array An array of hashes where each hash contains the details of
     *               a single photo.
     */
    public function getPhotos($album) {
        $key = 'FLICKR_PHOTOS_' . $album;
        $photos = Cache::get($key);
        if (!$photos) {
            $album_id = $this->getAlbum($album);
            $album_id = $album_id['set'];
            $photos = array();
            $index = 0;
            $result = $this->photosets->getPhotos(
                array(
                    'photoset_id' => $album_id,
                    'extras' => 'url_o,url_q,url_m'
                )
            );
            foreach ($result['photoset']['photo'] as $photo) {
                $photos[$index] = array(
                    'album' => $album,
                    'index' => $index,
                    'thumbnail' => $photo['url_q'],
                    'large' => $photo['url_m'],
                    'fullsize' => $photo['url_o'],
                    'title' => $photo['title'],
                );
                $index++;
            }
            Cache::set($key, $photos, 86400);
        }
        return $photos;
    }

    /**
     * Get the NSID for our current user.
     *
     * @return string The NSID for the current user.
     */
    private function getCurrentUserNSID() {
        $response = $this->people->findByUsername(
            array('username' => $this->_username)
        );
        return $response['user']['nsid'];
    }

    /**
     * Get the thumbnail for a specific photo.
     *
     * @param string $photo_id The ID of the photo.
     *
     * @return string The URL of the thumbnail.
     */
    private function getThumbnail($photo_id) {
        $response = $this->photos->getSizes(array('photo_id' => $photo_id));
        $url = null;
        foreach ($response['sizes']['size'] as $size) {
            $url = $size['source'];
            if ($size['label'] == 'Large Square') {
                break;
            }
        }
        return $url;
    }

    /**
     * Make a request to the Flickr API and decode the result.
     *
     * @param string $method The method to call.
     * @param string $args   The arguments to pass to the method.
     *
     * @return array The decoded version of the JSON response.
     */
    public function request($method, $args=array()) {
        $url = $this->constructAPIURL($method, $args);
        $key = 'FLICKR_API_REQUEST' . md5($url);
        $result = Cache::get($key);
        if (!$result) {
            $result = JSON::decode(file_get_contents($url), true);
            if ($result !== null) {
                if ($result['stat'] == 'fail') {
                    throw new Exception(
                        $result['message'],
                        $this->mapErrorCode($method, $result['code'])
                    );
                }
                Cache::set($key, $result, 3600);
            }
        }
        return $result;
    }

    /**
     * Magic to allow us to treat property accesses as calls to the Flickr API.
     *
     * @param string $name The name of the property to fetch.
     *
     * @return FlickrMagic A callable object which will fetch from the API.
     */
    public function __get($name) {
        $this->$name = new FlickrMagic($this, $name);
        return $this->$name;
    }

    /**
     * Build a URL for a given method (and optionally arguments) in the Flickr
     * API.
     *
     * @param string $method The method to call.
     * @param string $args   The arguments to pass to the method.
     *
     * @return string The URL for the method in the Flickr API.
     */
    private function constructAPIURL($method, $args=array()) {
        $api_key = preg_replace('/^[^0-9a-fA-F]+$/', '', $this->_api_key);
        $method = preg_replace('/^\W$/', '', $method);

        $url  = "http://api.flickr.com/services/rest/";
        $url .= "?api_key=$api_key";
        $url .= "&format=json";
        $url .= "&nojsoncallback=1";
        $url .= "&method=$method";
        foreach ($args as $key => $value) {
            $url .= "&" . rawurlencode($key) . "=" . rawurlencode($value);
        }
        return $url;
    }

    /**
     * Return the most appropriate HTTP status code for a given Flickr error
     * code.
     *
     * @param string $method              The Flickr API method which was
     *                                    called.
     * @param int    $flickr_error_number The Flickr error code which was
     *                                    returned.
     *
     * @return int The most appropriate HTTP status code or 500 if none can be
     *             calculated.
     */
    private function mapErrorCode($method, $flickr_error_number) {
        $error_map = array(
            'flickr.people.findByUsername' => array(1 => 404, 105 => 503),
            'flickr.photosets.getList' => array(1 => 404, 105 => 503),
            'flickr.photosets.getPhotos' => array(1 => 404, 105 => 503),
        );

        $code = 500;
        if (array_key_exists($method, $error_map)) {
            if (array_key_exists($flickr_error_number, $error_map[$method])) {
                $code = $error_map[$method][$flickr_error_number];
            }
        }
        return $code;
    }
}

/** A callable for helping the magic __get in Flickr. */
class FlickrMagic {
    /**
     * Create a new helper.
     *
     * @param Flickr $flickr The Flickr object to augment.
     * @param string $name   The name of the property in Flickr.
     */
    public function __construct($flickr, $name) {
        $this->flickr = $flickr;
        $this->name = $name;
    }

    /**
     * Actually call out to Flickr.
     *
     * @param string $method The method to call.
     * @param string $args   The arguments to pass to the method.
     *
     * @return array The decoded version of the JSON response from the method
     *               call.
     */
    public function __call($method, $args) {
        return $this->flickr->request(
            "flickr.{$this->name}.$method",
            $args[0]
        );
    }
}

?>
