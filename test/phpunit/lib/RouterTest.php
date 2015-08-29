<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for Router.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class RouterTest
extends TestCase
{
    /**
     * Test Router#dispatch and make sure it delegates to Klein.
     *
     * @return void
     */
    public function testDispatch() {
        $router = new Router(array(), new FakeKlein());

        $request = new \Klein\Request();
        $response = new \Klein\Response();

        $this->assertEquals(array(), $router->dispatch());
        $this->assertEquals(array($request), $router->dispatch($request));
        $this->assertEquals(
            array($request, $response),
            $router->dispatch($request, $response)
        );
        $this->assertEquals(
            array($request, $response, true),
            $router->dispatch($request, $response, true)
        );
        $this->assertEquals(
            array($request, $response, true, \Klein\Klein::DISPATCH_NO_CAPTURE),
            $router->dispatch(
                $request,
                $response,
                true,
                \Klein\Klein::DISPATCH_NO_CAPTURE
            )
        );
    }

    /**
     * Test Router#loadRoute
     *
     * @return void
     */
    public function testLoadRoute() {
        $klein = new FakeKlein();
        $router = new Router(array(), $klein);
        $router->loadRoute(
            '@.*',
            array('method' => 'GET', 'controller' => 'Dummy')
        );
        $this->assertRoute($klein, '@.*', 'GET');
    }

    /**
     * Test Router#loadRoute with a route with no method
     *
     * @return void
     */
    public function testLoadRouteNoMethod() {
        $klein = new FakeKlein();
        $router = new Router(array(), $klein);
        $router->loadRoute('@.*', array('controller' => 'Dummy'));
        $this->assertRoute($klein, '@.*', 'GET');
    }

    /**
     * Simple test of loadRedirectRoute.
     *
     * @return void
     */
    public function testLoadRedirectRoute() {
        $klein = new \Klein\Klein();
        $router = new Router(array(), $klein);
        $router->loadRedirectRoute(
            '/',
            '/about'
        );
        $this->assertRedirectRoute($klein, '/', '/about');
    }

    /**
     * Test of loadRedirectRoute with parameters passed to the redirected URL.
     *
     * @return void
     */
    public function testLoadRedirectParams() {
        $klein = new \Klein\Klein();
        $router = new Router(array(), $klein);
        $router->loadRedirectRoute(
            '/',
            '/about',
            array('foo' => 'bar')
        );
        $this->assertRedirectRoute($klein, '/', '/about?foo=bar');
    }

    /**
     * Test of loadRedirectRoute with parameters from the request passed to the
     * redirected URL.
     *
     * @return void
     */
    public function testLoadRedirectReqParams() {
        $klein = new \Klein\Klein();
        $router = new Router(array(), $klein);
        $router->loadRedirectRoute(
            '/',
            '/about',
            array('foo' => 'req->foo')
        );
        $this->assertRedirectRoute($klein, '/?foo=bar&baz=quux', '/about?foo=bar');
    }

    /**
     * Test of loadRedirectRoute with a URL fragment sent to the redirected
     * URL.
     *
     * @return void
     */
    public function testLoadRedirectFragment() {
        $klein = new \Klein\Klein();
        $router = new Router(array(), $klein);
        $router->loadRedirectRoute(
            '/',
            '/about',
            null,
            'req->foo'
        );
        $this->assertRedirectRoute($klein, '/?foo=bar', '/about#bar');
    }

    /**
     * Test loadRoutes with a simple route.
     *
     * @return void
     */
    public function testLoadRoutesSimple() {
        $this->assertLoadRoutes(
            array('/' => 'TechController'),
            '/',
            200,
            '/GitHub/'
        );
    }

    /**
     * Test loadRoutes with a redirect route.
     *
     * @return void
     */
    public function testLoadRoutesRedirect() {
        $this->assertLoadRoutes(
            array(
                '/' => array('redirect' => 'http://www.shooting.ie/')
            ),
            '/',
            301,
            '/www.shooting.ie/'
        );
    }

    /**
     * Test Router#onError with an exception producing a 404 error.
     *
     * @return void
     */
    public function testOnError404() {
        $this->onErrorTest('TextException404Controller', 404);
    }

    /**
     * Test Router#onError with an exception producing a 500 error.
     *
     * @return void
     */
    public function testOnError500() {
        $this->onErrorTest('TextException500Controller', 500);
    }

    /**
     * Test exception tracking in the onError function.
     *
     * @return void
     */
    public function testOnErrorExceptionTracking() {
        $tracker = ExceptionTracker::getInstance();
        $tracker->lastException = null;
        $this->onErrorTest('TextException8000Controller', 500);
        $this->assertNotNull($tracker->lastException);
        $this->assertEquals(8000, $tracker->lastException->getCode());
    }

    /**
     * Test Router#onError.
     *
     * @param string $controller    The name of the controller to test.
     * @param int    $expected_code The expected HTTP status code.
     *
     * @return void
     */
    private function onErrorTest($controller, $expected_code) {
        $router = new Router(array('/' => $controller));
        $res = new \Klein\Response();

        $router->dispatch($this->constructRequest('/'), $res, false);

        $this->assertRegexp("/" . $controller . "/", $res->body());
        $this->assertEquals($expected_code, $res->code());
    }

    /**
     * Check that a route exists and has the right method.
     *
     * @param \Klein\Klein $klein  The instance of Klein behind Router.
     * @param string       $path   The route path.
     * @param string       $method The HTTP method which the route should
     *                             respond to.
     *
     * @return void
     */
    private function assertRoute($klein, $path, $method = 'GET') {
        $found_route = false;
        foreach ($klein->routes() as $route) {
            if ($route->getPath() === $path) {
                $found_route = true;
                $this->assertEquals($method, $route->getMethod());
            }
        }
        $this->assertTrue($found_route);
    }

    /**
     * Ensure that a redirection route does the right thing and sets the
     * Location and returns a 301.
     *
     * @param \Klein\Klein $klein       The instance of Klein behind Router.
     * @param string       $path        The URI to fetch to trigger the
     *                                  redirect.
     * @param string       $destination The location that the redirect should
     *                                  point to.
     *
     * @return void
     */
    private function assertRedirectRoute($klein, $path, $destination) {
        $req = $this->constructRequest($path);
        $res = new \Klein\Response();
        $klein->dispatch($req, $res, false);
        $this->assertEquals(301, $res->status()->getCode());
        $this->assertEquals($destination, $res->headers()->get('Location'));
    }

    /**
     * Test loadRoutes by passing an array of Routes to a fresh copy of Router
     * and then exercising that Router. Look for a successful response and that
     * the body matches the supplied pattern.
     *
     * @param array  $routes  The routes to load.
     * @param string $path    The path to request when testing.
     * @param int    $status  The expected status.
     * @param string $pattern A pattern to match against the body or against
     *                        the Location header if the expected status is 301
     *                        or 304.
     *
     * @return void
     */
    private function assertLoadRoutes($routes, $path, $status, $pattern) {
        $klein = new \Klein\Klein();
        $router = new Router($routes, $klein);
        $this->assertNotNull($router);
        $res = new \Klein\Response();
        $klein->dispatch($this->constructRequest($path), $res, false);
        $res_status = $res->status()->getCode();
        $this->assertEquals(
            $status,
            $res_status,
            "$path returned $res_status, expected $status"
        );
        if ($status == 301 || $status == 304) {
            $this->assertRegexp($pattern, $res->headers()->get('Location'));
        } else {
            $this->assertRegexp($pattern, $res->body());
        }
    }

    /**
     * Construct a \Klein\Request from a URL.
     *
     * @param string $url A URL path, optionally with query parameters.
     *
     * @return \Klein\Request A \Klein\Request object initialised to match the
     *                        supplied URL.
     */
    private function constructRequest($url) {
        $url = parse_url($url);
        $query_params = array();
        if (array_key_exists('query', $url)) {
            foreach (explode('&', $url['query']) as $param) {
                list($key, $value) = explode('=', $param, 2);
                $query_params[$key] = $value;
            }
        }
        return new \Klein\Request(
            $query_params,
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => $url['path']
            )
        );
    }
}

/**
 * A dummy controller which will always raise an exception.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestExceptionController
extends Controller
{
    /**
     * Throw an exception to make the get method barf.
     *
     * @return void
     */
    protected function content() {
        throw new Exception(get_class($this), $this->code());
    }

    /**
     * Figure out which code should be used for the exception.
     *
     * @return The number that's in the name of the class.
     */
    protected function code() {
        return preg_replace('/\D+/', '', get_class($this));
    }
}

/**
 * A dummy controller which will always raise an exception with a status code
 * of 404.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TextException404Controller
extends TestExceptionController
{
}

/**
 * A dummy controller which will always raise an exception with a status code
 * of 500.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TextException500Controller
extends TestExceptionController
{
}

/**
 * A dummy controller which will always raise an exception with a status code
 * of 8000 which should result in a HTTP 500.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TextException8000Controller
extends TestExceptionController
{
}

?>
