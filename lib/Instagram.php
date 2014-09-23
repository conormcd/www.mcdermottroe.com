<?php

/**
 * A simple interface to Instagram.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Instagram {
    /**
     * Get an instance of this class.
     *
     * @return Instagram An instance of this class.
     */
    public static function getInstance() {
        try {
            $class_name = Environment::get('INSTAGRAM_CLASS');
        } catch (Exception $e) {
            $class_name = get_class();
        }
        return new $class_name(
            Environment::get('INSTAGRAM_CLIENT_ID'),
            Environment::get('INSTAGRAM_CLIENT_SECRET'),
            Environment::get('INSTAGRAM_USER_ID')
        );
    }

    /**
     * Create a new facade over Instagram.
     *
     * @param string $client_id     The Instagram client ID.
     * @param string $client_secret The Instagram client secret.
     * @param int    $user_id       The Instagram user ID for me.
     */
    public function __construct($client_id, $client_secret, $user_id) {
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;
        $this->_user_id = $user_id;
        $this->_http_client = HTTPClient::getInstance();
    }

    /**
     * Get the stream of recent photos.
     *
     * @return array An array of associative arrays, each of which is a
     *               reference to a photo on Instagram.
     */
    public function getStream() {
        $url  = "https://api.instagram.com/v1/users/{$this->_user_id}/media/recent";
        $url .= "?client_id={$this->_client_id}";
        $url .= "&client_secret={$this->_client_secret}";

        $key = 'INSTAGRAM_API_REQUEST' . md5($url);
        $images = Cache::get($key);
        if (!$images) {
            $images = array();
            try {
                $stream = JSON::decode($this->_http_client->get($url));
            } catch (Exception $e) {
                ExceptionTracker::getInstance()->captureException($e);
                return $images;
            }
            if ($stream !== null && array_key_exists('data', $stream)) {
                foreach ($stream['data'] as $image) {
                    $images[] = array(
                        'timestamp' => $image['created_time'],
                        'dateISO8601' => Time::dateISO8601($image['created_time']),
                        'link' => $image['link'],
                        'image' => $image['images']['standard_resolution']['url'],
                        'caption' => $image['caption']['text'],
                        'title' => Time::day($image['created_time']),
                        'isInstagramPhoto' => true,
                    );
                }
                Cache::set($key, $images, 3600 + rand(0, 300));
            }
        }
        return $images;
    }
}

?>
