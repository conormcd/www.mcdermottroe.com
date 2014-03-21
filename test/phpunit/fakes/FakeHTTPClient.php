<?php

/**
 * A fake for HTTPClient
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeHTTPClient
extends HTTPClient
{
    private static $_responses = null;

    /**
     * Add a response to a specific URL pattern.
     *
     * @param string $url_pattern A regular expression matching a set of URLs
     *                            to respond to.
     * @param mixed  $response    Either a string or a callable that generates
     *                            a string which should be supplied to anything
     *                            fetching a URL matching the pattern.
     *
     * @return void
     */
    public static function addResponse($url_pattern, $response) {
        if (!is_array(self::$_responses)) {
            self::$_responses = array();
        }
        if (!is_callable($response)) {
            if (is_string($response)) {
                $response = function () use ($response) {
                    return $response;
                };
            } else {
                throw new Exception("Invalid response type");
            }
        }
        self::$_responses[] = array(
            'pattern' => $url_pattern,
            'callable' => $response,
        );
    }

    /**
     * Remove all fake responses.
     *
     * @return void
     */
    public static function reset() {
        self::$_responses = null;
    }

    /**
     * Trap outgoing GET requests.
     *
     * @param string $url The URL to pretend to fetch.
     *
     * @return string The faked out contents of the URL.
     */
    public function get($url) {
        if (self::$_responses) {
            foreach (self::$_responses as $response) {
                if (preg_match($response['pattern'], $url)) {
                    $ret = call_user_func($response['callable'], array($url));

                    // Normalise the return value
                    if (is_string($ret)) {
                        $ret = array('status' => 200, 'body' => $ret);
                    } else if (is_array($ret)) {
                        if (!array_key_exists('status', $ret)) {
                            throw new Exception('Bad fake return, no status');
                        }
                    }

                    if ($ret['status'] !== 200) {
                        throw new Exception($ret['body'], $ret['status']);
                    }

                    return $ret['body'];
                }
            }
        }
        throw new Exception("No fake response recorded for $url");
    }
}

?>
