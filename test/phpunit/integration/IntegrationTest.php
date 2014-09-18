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
     * Make sure the requests are fast-ish on average.
     *
     * @return void
     */
    public function testSpeed() {
        $threshold = 50;
        $sum = 0;
        $count = 0;
        foreach (array_values(IntegrationRequests::times()) as $time) {
            $sum += $time;
            $count++;
        }
        $this->assertLessThan($threshold, $sum / $count);
    }

    /**
     * Test URLs that should result in a 404.
     *
     * @return void
     */
    public function test404() {
        $urls = array(
            '/does_not_exist',             // No route
            '/blog/2000/01/01/nope/',      // BlogModel throws 404
            '/blog/?page=-1',              // PageableModel throws 404
            '/blog/?page=1000000',         // PageableModel throws 404
            '/css/does_not_exist.css',     // StaticFileModel throws 404
        );
        foreach ($urls as $url) {
            $res = IntegrationRequests::get($url);
            $this->assertEquals(404, $res->status()->getCode());
            $this->assertEquals(
                'public max-age=0',
                $res->headers()->get('Cache-Control')
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
                for ($i = 0; $i < 2; $i++) {
                    $start = microtime(true);
                    self::$_responses[$url] = self::get($url);
                    self::$_times[$url] = (microtime(true) - $start) * 1000;
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
    public static function get($url) {
        global $ROUTES;

        $url = self::parseURL($url);

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
     * @return array An array of URLs to be fetched.
     */
    private static function urls() {
        $sitemap = dirname(dirname(dirname(__DIR__))) . '/public/google-sitemap.xml';
        $sitemap = simplexml_load_file($sitemap);
        $urls = array();
        foreach ($sitemap->url as $url) {
            $urls[] = "{$url->loc}";
        }
        return $urls;
    }

    /**
     * A wrapper around parse_url which also expands the query parameters into
     * an associative array.
     *
     * @param string $url The URL to parse.
     *
     * @return array The exact sam structure as returned by parse_url except
     *               that the 'query' element is now an associative array of
     *               all the query parameters included in the original URL.
     */
    private static function parseURL($url) {
        $url = parse_url($url);
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
        return $url;
    }
}

?>
