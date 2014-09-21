<?php

/**
 * A simple interface to Flickr.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Flickr
extends PhotoProvider
{
    private $_api_key;

    private $_api_secret;

    private $_username;

    private $_http_client;

    /**
     * Get an instance of this class, configured from the environment.
     *
     * @return Flickr A working Flickr instance.
     */
    public static function getInstance() {
        return new Flickr(
            Environment::get('FLICKR_API_KEY'),
            Environment::get('FLICKR_API_SECRET'),
            Environment::get('FLICKR_API_USER')
        );
    }

    /**
     * Create a new Flickr interface. You can find the credentials in the
     * environment.
     *
     * @param string $key    The API key for Flickr.
     * @param string $secret The API secret for Flickr.
     * @param string $user   The Flickr user from whom to copy the photos.
     */
    public function __construct($key, $secret, $user) {
        $this->_api_key = $key;
        $this->_api_secret = $secret;
        $this->_username = $user;
        $this->_http_client = HTTPClient::getInstance();
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PhotoProvider#getAlbum()}
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
                $albums[] = new PhotoAlbumModel(
                    $this,
                    $set['id'],
                    $set['title']['_content'],
                    $set['date_create'],
                    $set['primary']
                );
            }
            Cache::set($key, $albums, 3600);
        }
        return $albums;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $album_short_name See {@link PhotoProvider#getAlbum()}
     *
     * @return PhotoAlbumModel See {@link PhotoProvider#getAlbum()}
     */
    public function getAlbum($album_short_name) {
        $albums = $this->getAlbums();
        foreach ($albums as $album) {
            if ($album->slug() == $album_short_name) {
                return $album;
            }
        }
        throw new Exception(
            "Couldn't find the Flickr set \"$album_short_name\"",
            404
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param string $album See {@link PhotoProvider#getAlbum()}
     *
     * @return array See {@link PhotoProvider#getAlbum()}
     */
    public function getPhotos($album) {
        $key = 'FLICKR_PHOTOS_' . $album->slug();
        $photos = Cache::get($key);
        if (!$photos) {
            $photos = array();
            $index = 0;
            $result = $this->photosets->getPhotos(
                array(
                    'photoset_id' => $album->albumID(),
                    'extras' => 'url_o,url_q,url_c,url_z,url_m'
                )
            );
            foreach ($result['photoset']['photo'] as $photo) {
                $sizes = array(
                    'thumbnail' => $photo['url_q'],
                    'fullsize' => $photo['url_o']
                );
                foreach (array('c', 'z', 'm', 'o') as $size) {
                    if (array_key_exists("url_$size", $photo)) {
                        $sizes['large'] = $photo["url_$size"];
                        break;
                    }
                }
                $photos[$index] = new PhotoModel(
                    $album,
                    $photo['id'],
                    $index,
                    $photo['title'],
                    $sizes
                );
                $index++;
            }
            Cache::set($key, $photos, $this->albumCacheLifetime($album));
        }
        return $photos;
    }

    /**
     * Work out how long we should cache photos for. Older albums get cached
     * for longer and a random amount is added to each value to discourage lots
     * of entries from expiring at the same time.
     *
     * @param PhotoAlbumModel $album The album.
     *
     * @return int The number of seconds we should cache a particular album
     *             for.
     */
    private function albumCacheLifetime($album) {
        $album_age = time() - $album->timestamp();
        if ($album_age > (86400 * 365)) {
            return 0;
        } else if ($album_age > (86400 * 30)) {
            return (86400 * 30) + rand(0, 86400);
        } else {
            return 86400 + rand(0, 3600);
        }
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
            $result = JSON::decode($this->_http_client->get($url));
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

        $url  = "https://api.flickr.com/services/rest/";
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
