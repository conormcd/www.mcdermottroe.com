<?php

require_once dirname(dirname(dirname(__DIR__))) . '/config/routes.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * A collection of tests that attempt to exercise the site as a whole. All the
 * links in the Google Sitemap are run in as realistic a setup as we can
 * achieve.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class IntegrationTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Ensure that all the requests return a 200.
     *
     * @return void
     */
    public function testAll200() {
        foreach (IntegrationRequests::responses() as $url => $res) {
            $this->assertEquals(
                200,
                $res->status()->getCode(),
                "$url did not return 200: " . $res->body()
            );
        }
    }

    /**
     * Ensure that every page sets a Cache-Control header and caches for some
     * non-zero amount of time.
     *
     * @return void
     */
    public function testCacheControl() {
        foreach (IntegrationRequests::responses() as $url => $res) {
            $cache_control = $res->headers()->get('Cache-Control');
            $this->assertNotNull(
                $cache_control,
                "$url did not have a Cache-Control header"
            );
            $this->assertRegexp(
                '/^public max-age=\d+$/',
                $cache_control,
                "$url had an unexpected Cache-Control header"
            );
            $this->assertGreaterThan(
                0,
                preg_replace('/^public max-age=/', '', $cache_control) + 0,
                "$url is not set up to cache"
            );
        }
    }

    /**
     * Double check that the Content-Length header matches the actual length of
     * the content.
     *
     * @return void
     */
    public function testContentLength() {
        foreach (IntegrationRequests::responses() as $url => $res) {
            $this->assertNotNull(
                $res->headers()->get('Content-Length'),
                "$url did not have a Content-Length header"
            );
            $this->assertEquals(
                strlen($res->body()),
                $res->headers()->get('Content-Length'),
                "$url had a mismatched Content-Length header"
            );
        }
    }

    /**
     * Sanity check the Content-Type header.
     *
     * @return void
     */
    public function testContentType() {
        $content_types = array(
            'css' => 'text/css',
            'ico' => 'image/x-icon',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'rdf' => 'application/xml',
            'txt' => 'text/plain',
            'xls' => 'application/vnd.ms-office',
        );
        foreach (IntegrationRequests::responses() as $url => $res) {
            $content_type = 'text/html';
            $extension = preg_replace('/^.*\./', '', $url);
            if (array_key_exists($extension, $content_types)) {
                $content_type = $content_types[$extension];
            }

            $this->assertNotNull(
                $res->headers()->get('Content-Type'),
                "$url did not have a Content-Type header"
            );
            $this->assertEquals(
                $content_type,
                $res->headers()->get('Content-Type'),
                "$url had a unexpected Content-Type header"
            );
        }
    }

    /**
     * Make sure we have no egregiously slow requests.
     *
     * @return void
     */
    public function testSpeed() {
        $threshold = 100;
        foreach (IntegrationRequests::times() as $url => $time) {
            $this->assertLessThan(
                $threshold,
                $time,
                "$url took more than $threshold milliseconds to run"
            );
        }
    }
}

/**
 * We only want to make each request at most once, so this class wraps up those
 * requests so that we can have the illusion of making them independently in
 * each test above.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class IntegrationRequests
{
    /** The responses from the requests. */
    private static $_responses = null;

    /** The amount of time each request took, in milliseconds. */
    private static $_times = null;

    /**
     * Get all the responses for the requests.
     *
     * @return array An associative array where the keys are URLs and the
     *               values are \Klein\Response objects representing the
     *               response to processing that URL.
     */
    public static function responses() {
        self::makeRequests();
        return self::$_responses;
    }

    /**
     * Get all the response times for the requests.
     *
     * @return array An associative array where the keys are URLs and the
     *               values are the times each URL got fetched, in
     *               milliseconds.
     */
    public static function times() {
        self::makeRequests();
        return self::$_times;
    }

    /**
     * Issue all of the requests.
     *
     * @return void
     */
    private static function makeRequests() {
        if (self::$_responses === null) {
            self::$_responses = array();
            self::$_times = array();

            Environment::load();

            foreach (self::urls() as $url) {
                $key = self::unparseURL($url);

                for ($i = 0; $i < 2; $i++) {
                    $start = microtime(true);
                    self::$_responses[$key] = self::get($url);
                    self::$_times[$key] = (microtime(true) - $start) * 1000;
                }
            }
        }
    }

    /**
     * Run a single URL.
     *
     * @param string $url The URL to fetch.
     *
     * @return \Klein\Response The response returned by the site.
     */
    private static function get($url) {
        global $ROUTES;

        $req = new \Klein\Request(
            $url['query'],
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $url['path']
            )
        );
        $res = new \Klein\Response();

        $klein = new \Klein\Klein();
        $router = new Router($ROUTES, $klein);
        $router->dispatch($req, $res, false);

        return $res;
    }

    /**
     * Get all the URLs to be tested.
     *
     * @return array An array where each element is an array representing the
     *               parsed form of the URL.
     */
    private static function urls() {
        $sitemap = dirname(dirname(dirname(__DIR__))) . '/public/google-sitemap.xml';
        $sitemap = simplexml_load_file($sitemap);
        $urls = array();
        foreach ($sitemap->url as $url) {
            $url = parse_url($url->loc);
            if (array_key_exists('query', $url)) {
                $query_params = array();
                foreach (explode('&', $url['query']) as $param) {
                    list($key, $value) = explode('=', $param, 2);
                    $query_params[$key] = $value;
                }
                $url['query'] = $query_params;
            } else {
                $url['query'] = array();
            }
            $urls[] = $url;
        }
        return $urls;
    }

    /**
     * The reverse of parse_url, or at least the parts of it that we use.
     *
     * @param array $url The output of parse_url.
     *
     * @return string The assembled URL.
     */
    private static function unparseURL($url) {
        $result = '';
        if (array_key_exists('host', $url)) {
            $result = $url['host'];
            if (array_key_exists('port', $url)) {
                $result .= ":" . $url['port'];
            }
            if (array_key_exists('scheme', $url)) {
                $result = $url['scheme'] . '://' . $result;
            } else {
                $result = 'http://' . $result;
            }
        }
        if (array_key_exists('path', $url)) {
            $result .= $url['path'];
        }
        if (array_key_exists('query', $url)) {
            $query_params = array();
            foreach ($url['query'] as $key => $value) {
                $query_params[] = $key . '=' . $value;
            }
            if ($query_params) {
                $query_params = join('&', $query_params);
                $result .= '?' . $query_params;
            }
        }
        if (array_key_exists('fragment', $url)) {
            $result .= '#' . $url['fragment'];
        }
        return $result;
    }
}

?>
