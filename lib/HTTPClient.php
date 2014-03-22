<?php

/**
 * A HTTP client for making requests to external services.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class HTTPClient {
    /**
     * Get a HTTP client instance.
     *
     * @return HTTPClient An instance of this class.
     */
    public static function getInstance() {
        try {
            $class_name = Environment::get('HTTP_CLIENT_CLASS');
        } catch (Exception $e) {
            $class_name = get_class();
        }
        return new $class_name();
    }

    /**
     * Execute a HTTP GET request.
     *
     * @param string $url The URL to fetch.
     *
     * @return string The contents returned by the server serving that URL.
     */
    public function get($url) {
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'www.mcdermottroe.com',
        );

        $curl = curl_init();
        curl_setopt_array($curl, $curl_options);
        $response = curl_exec($curl);

        $info = curl_getinfo($curl);
        if ($info['http_code'] >= 400) {
            throw new Exception("$url returned {$info['http_code']}");
        }

        return $response;
    }
}

?>
