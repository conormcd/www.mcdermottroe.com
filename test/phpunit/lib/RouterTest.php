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
        $req = new \Klein\Request(
            array(),
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/'
            )
        );
        $res = new \Klein\Response();

        $router->dispatch($req, $res, false);

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
            if ($route[1] === $path) {
                $found_route = true;
                $this->assertEquals($method, $route[0]);
            }
        }
        $this->assertTrue($found_route);
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
     * See Controller::__construct for details.
     *
     * @param \Klein\Request  $req The Klein request object.
     * @param \Klein\Response $res The Klein response object.
     */
    public function __construct($req, $res) {
        $this->action = 'dummy';
        parent::__construct($req, $res);
    }

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
